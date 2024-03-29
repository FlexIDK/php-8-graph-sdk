<?php

namespace One23\GraphSdk\Exceptions;

use One23\GraphSdk\Response;
use One23\GraphSdk\Traits\MapTypeTrait;

class ResponseException extends SDKException
{
    use MapTypeTrait;

    protected array $responseData;

    public function __construct(protected Response $response, SDKException $previousException = null)
    {
        $this->responseData = $response->getDecodedBody();

        $errorMessage   = self::mapType(
            $this->get('message', 'Unknown error from Graph.'),
            'str'
        );
        $errorCode      = self::mapType(
            $this->get('code'),
            'int',
            -1
        );

        parent::__construct($errorMessage, $errorCode, $previousException);
    }

    /**
     * Checks isset and returns that or a default value.
     */
    private function get(string $key, $default = null): mixed
    {
        return $this->responseData['error'][$key] ?? $default;
    }

    /**
     * A factory for creating the appropriate exception based on the response from Graph.
     */
    public static function create(Response $response): static
    {
        $data = $response->getDecodedBody();

        if (!isset($data['error']['code']) && isset($data['code'])) {
            $data = ['error' => $data];
        }

        $code    = $data['error']['code'] ?? null;
        $message = $data['error']['message'] ?? 'Unknown error from Graph.';

        if (isset($data['error']['error_subcode'])) {
            $subcode = self::mapType(
                $data['error']['error_subcode'],
                'int',
                -1
            );

            switch ($data['error']['error_subcode']) {
                // Other authentication issues
                case 458:
                case 459:
                case 460:
                case 463:
                case 464:
                case 467:
                    return new static($response, new AuthenticationException($message, $code));

                // Video upload resumable error
                case 1363030:
                case 1363019:
                case 1363033:
                case 1363021:
                case 1363041:
                    return new static($response, new ResumableUploadException($message, $code));

                case 1363037:
                    $previousException = new ResumableUploadException($message, $code);

                    $startOffset = isset($data['error']['error_data']['start_offset']) ? (int)$data['error']['error_data']['start_offset'] : null;
                    $previousException->setStartOffset($startOffset);

                    $endOffset = isset($data['error']['error_data']['end_offset']) ? (int)$data['error']['error_data']['end_offset'] : null;
                    $previousException->setEndOffset($endOffset);

                    return new static($response, $previousException);
            }
        }

        $code = self::mapType(
            $code,
            'int'
        );
        switch ($code) {
            // Login status or token expired, revoked, or invalid
            case 100:
            case 102:
            case 190:
                return new static($response, new AuthenticationException($message, $code));

            // Server issue, possible downtime
            case 1:
            case 2:
                return new static($response, new ServerException($message, $code));

            // API Throttling
            case 4:
            case 17:
            case 32:
            case 341:
            case 613:
                return new static($response, new ThrottleException($message, $code));

            // Duplicate Post
            case 506:
                return new static($response, new ClientException($message, $code));
        }

        // Missing Permissions
        if ($code == 10 || ($code >= 200 && $code <= 299)) {
            return new static($response, new AuthorizationException($message, $code));
        }

        // OAuth authentication error
        if (isset($data['error']['type']) && $data['error']['type'] === 'OAuthException') {
            return new static($response, new AuthenticationException($message, $code));
        }

        // All others
        return new static($response, new OtherException($message, $code));
    }

    /**
     * Returns the HTTP status code
     */
    public function getHttpStatusCode(): int
    {
        return $this->response->getHttpStatusCode();
    }

    /**
     * Returns the sub-error code
     */
    public function getSubErrorCode(): int
    {
        return self::mapType(
            $this->get('error_subcode'),
            'int',
            -1
        );
    }

    /**
     * Returns the error type
     */
    public function getErrorType(): string
    {
        return self::mapType(
            $this->get('type'),
            'str',
            ''
        );
    }

    /**
     * Returns the raw response used to create the exception.
     */
    public function getRawResponse(): string
    {
        return $this->response->getBody();
    }

    /**
     * Returns the decoded response used to create the exception.
     */
    public function getResponseData(): array
    {
        return $this->responseData;
    }

    /**
     * Returns the response entity used to create the exception.
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
