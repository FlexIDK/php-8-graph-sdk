<?php

namespace One23\GraphSdk;

use One23\GraphSdk\GraphNodes\GraphAlbum;
use One23\GraphSdk\GraphNodes\GraphEdge;
use One23\GraphSdk\GraphNodes\GraphEvent;
use One23\GraphSdk\GraphNodes\GraphGroup;
use One23\GraphSdk\GraphNodes\GraphNode;
use One23\GraphSdk\GraphNodes\GraphNodeFactory;
use One23\GraphSdk\Exceptions\ResponseException;
use One23\GraphSdk\Exceptions\SDKException;
use One23\GraphSdk\GraphNodes\GraphPage;
use One23\GraphSdk\GraphNodes\GraphSessionInfo;
use One23\GraphSdk\GraphNodes\GraphUser;

class Response
{
    /**
     * The decoded body of the Graph response.
     */
    protected array $decodedBody = [];

    /**
     * The exception thrown by this request.
     */
    protected SDKException $thrownException;

    public function __construct(protected Request $request, protected ?string $body = null, protected ?int $httpStatusCode = null, protected array $headers = [])
    {
        $this->decodeBody();
    }

    /**
     * Convert the raw response into an array if possible.
     *
     * Graph will return 2 types of responses:
     * - JSON(P)
     *    Most responses from Graph are JSON(P)
     * - application/x-www-form-urlencoded key/value pairs
     *    Happens on the `/oauth/access_token` endpoint when exchanging
     *    a short-lived access token for a long-lived access token
     * - And sometimes nothing :/ but that'd be a bug.
     */
    public function decodeBody(): void
    {
        $decodedBody = json_decode($this->body, true);

        if(json_last_error() !== JSON_ERROR_NONE) {
            $this->decodedBody = [];
            parse_str($this->body, $this->decodedBody);
        }
        elseif (is_bool($decodedBody)) {
            // Backwards compatibility for Graph < 2.1.
            // Mimics 2.1 responses.
            $this->decodedBody = [
                'success' => $decodedBody
            ];
        }
        elseif (is_numeric($decodedBody)) {
            $this->decodedBody = [
                'id' => $decodedBody
            ];
        }
        elseif (!is_array($decodedBody)) {
            $this->decodedBody = [];
        }
        else {
            $this->decodedBody = $decodedBody;
        }

        if ($this->isError()) {
            $this->makeException();
        }
    }

    /**
     * Returns true if Graph returned an error message.
     */
    public function isError(): bool
    {
        return isset($this->decodedBody['error']);
    }

    /**
     * Instantiates an exception to be thrown later.
     */
    public function makeException(): void
    {
        $this->thrownException = ResponseException::create($this);
    }

    /**
     * Return the original request that returned this response.
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Return the App entity used for this response.
     */
    public function getApp(): App
    {
        return $this->request->getApp();
    }

    /**
     * Return the access token that was used for this response.
     */
    public function getAccessToken(): ?string
    {
        return $this->request->getAccessToken();
    }

    /**
     * Return the HTTP status code for this response.
     */
    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode ?? 0;
    }

    /**
     * Return the HTTP headers for this response.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Return the raw body response.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Return the decoded body response.
     */
    public function getDecodedBody(): array
    {
        return $this->decodedBody;
    }

    /**
     * Get the app secret proof that was used for this response.
     */
    public function getAppSecretProof(): ?string
    {
        return $this->request->getAppSecretProof();
    }

    /**
     * Get the ETag associated with the response.
     */
    public function getETag(): ?string
    {
        return $this->headers['ETag'] ?? null;
    }

    /**
     * Get the version of Graph that returned this response.
     */
    public function getGraphVersion(): ?string
    {
        return $this->headers['Facebook-API-Version'] ?? null;
    }

    /**
     * Throws the exception.
     *
     * @throws SDKException
     */
    public function throwException()
    {
        throw $this->thrownException;
    }

    /**
     * Returns the exception that was thrown for this request.
     */
    public function getThrownException(): ?SDKException
    {
        return $this->thrownException ?? null;
    }

    /**
     * Instantiate a new GraphNode from response.
     *
     * @throws SDKException
     */
    public function getGraphNode(string $subclassName = null): GraphNode
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphNode($subclassName);
    }

    /**
     * Convenience method for creating a GraphAlbum collection.
     *
     * @throws SDKException
     */
    public function getGraphAlbum(): GraphAlbum
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphAlbum();
    }

    /**
     * Convenience method for creating a GraphPage collection.
     *
     * @throws SDKException
     */
    public function getGraphPage(): GraphPage
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphPage();
    }

    /**
     * Convenience method for creating a GraphSessionInfo collection.
     *
     * @throws SDKException
     */
    public function getGraphSessionInfo(): GraphSessionInfo
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphSessionInfo();
    }

    /**
     * Convenience method for creating a GraphUser collection.
     *
     * @throws SDKException
     */
    public function getGraphUser(): GraphUser
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphUser();
    }

    /**
     * Convenience method for creating a GraphEvent collection.
     *
     * @throws SDKException
     */
    public function getGraphEvent(): GraphEvent
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphEvent();
    }

    /**
     * Convenience method for creating a GraphGroup collection.
     *
     * @throws SDKException
     */
    public function getGraphGroup(): GraphGroup
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphGroup();
    }

    /**
     * Instantiate a new GraphEdge from response.
     *
     * @throws SDKException
     */
    public function getGraphEdge(string $subclassName = null, bool $auto_prefix = true): GraphEdge
    {
        $factory = new GraphNodeFactory($this);

        return $factory->makeGraphEdge($subclassName, $auto_prefix);
    }
}
