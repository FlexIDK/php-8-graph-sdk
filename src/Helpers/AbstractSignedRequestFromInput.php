<?php

namespace One23\GraphSdk\Helpers;

use One23\GraphSdk\Exceptions\SDKException;
use One23\GraphSdk\Facebook;
use One23\GraphSdk\FacebookApp;
use One23\GraphSdk\FacebookClient;
use One23\GraphSdk\MapTypeTrait;
use One23\GraphSdk\SignedRequest;
use One23\GraphSdk\Authentication\AccessToken;
use One23\GraphSdk\Authentication\OAuth2Client;

abstract class AbstractSignedRequestFromInput
{
    use MapTypeTrait;

    protected ?SignedRequest $signedRequest;

    protected OAuth2Client $oAuth2Client;

    /**
     * @throws SDKException
     */
    public function __construct(
        protected FacebookApp $app,
        FacebookClient $client,
        string $graphVersion = null
    ) {
        $graphVersion = $graphVersion ?: Facebook::DEFAULT_GRAPH_VERSION;
        $this->oAuth2Client = new OAuth2Client($this->app, $client, $graphVersion);

        $this->instantiateSignedRequest();
    }

    /**
     * Instantiates a new SignedRequest entity.
     *
     * @throws SDKException
     */
    public function instantiateSignedRequest(string $rawSignedRequest = null): void
    {
        $rawSignedRequest = $rawSignedRequest ?: $this->getRawSignedRequest();

        if (!$rawSignedRequest) {
            return;
        }

        $this->signedRequest = new SignedRequest($this->app, $rawSignedRequest);
    }

    /**
     * Get raw signed request from input.
     */
    abstract public function getRawSignedRequest(): ?string;

    /**
     * Returns an AccessToken entity from the signed request.
     *
     * @throws SDKException
     */
    public function getAccessToken(): ?AccessToken
    {
        if ($this->signedRequest && $this->signedRequest->hasOAuthData()) {
            $code = self::mapType(
                $this->signedRequest->get('code'),
                'str'
            );
            $accessToken = self::mapType(
                $this->signedRequest->get('oauth_token'),
                'str'
            );

            if ($code && !$accessToken) {
                return $this->oAuth2Client->getAccessTokenFromCode($code);
            }

            $expiresAt = self::mapType(
                $this->signedRequest->get('expires'),
                'int',
                0
            );

            return new AccessToken(
                $accessToken,
                $expiresAt
            );
        }

        return null;
    }

    /**
     * Returns the SignedRequest entity.
     */
    public function getSignedRequest(): ?SignedRequest
    {
        return $this->signedRequest;
    }

    /**
     * Returns the user_id if available.
     */
    public function getUserId(): ?string
    {
        return $this->signedRequest?->getUserId();
    }

    /**
     * Get raw signed request from POST input.
     */
    public function getRawSignedRequestFromPost(): ?string
    {
        return self::mapType(
            $_POST['signed_request'] ?? null,
            'str'
        );
    }

    /**
     * Get raw signed request from cookie set from the Javascript SDK.
     */
    public function getRawSignedRequestFromCookie(): ?string
    {
        if (isset($_COOKIE['fbsr_' . $this->app->getId()])) {
            return self::mapType(
                $_COOKIE['fbsr_' . $this->app->getId()],
                'str'
            );
        }

        return null;
    }
}
