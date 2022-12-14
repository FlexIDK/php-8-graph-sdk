<?php

namespace One23\GraphSdk\Helpers;

use One23\GraphSdk\Facebook;
use One23\GraphSdk\FacebookApp;
use One23\GraphSdk\FacebookClient;
use One23\GraphSdk\SignedRequest;
use One23\GraphSdk\Authentication\AccessToken;
use One23\GraphSdk\Authentication\OAuth2Client;

abstract class FacebookSignedRequestFromInputHelper
{
    protected ?SignedRequest $signedRequest;

    protected OAuth2Client $oAuth2Client;

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
     */
    public function instantiateSignedRequest(string $rawSignedRequest = null)
    {
        $rawSignedRequest = $rawSignedRequest ?: $this->getRawSignedRequest();

        if (!$rawSignedRequest) {
            return;
        }

        $this->signedRequest = new SignedRequest($this->app, $rawSignedRequest);
    }

    /**
     * Get raw signed request from input.
     *
     * @return string|null
     */
    abstract public function getRawSignedRequest();

    /**
     * Returns an AccessToken entity from the signed request.
     *
     * @return AccessToken|null
     *
     * @throws \One23\GraphSdk\Exceptions\SDKException
     */
    public function getAccessToken()
    {
        if ($this->signedRequest && $this->signedRequest->hasOAuthData()) {
            $code = $this->signedRequest->get('code');
            $accessToken = $this->signedRequest->get('oauth_token');

            if ($code && !$accessToken) {
                return $this->oAuth2Client->getAccessTokenFromCode($code);
            }

            $expiresAt = $this->signedRequest->get('expires', 0);

            return new AccessToken(
                (string)$accessToken,
                (int)$expiresAt
            );
        }

        return null;
    }

    /**
     * Returns the SignedRequest entity.
     *
     * @return SignedRequest|null
     */
    public function getSignedRequest()
    {
        return $this->signedRequest;
    }

    /**
     * Returns the user_id if available.
     *
     * @return string|null
     */
    public function getUserId()
    {
        return $this->signedRequest ? $this->signedRequest->getUserId() : null;
    }

    /**
     * Get raw signed request from POST input.
     *
     * @return string|null
     */
    public function getRawSignedRequestFromPost(): ?string
    {
        $signedRequest = $_POST['signed_request'] ?? null;

        return $signedRequest ?
            (string)$signedRequest
            : null;
    }

    /**
     * Get raw signed request from cookie set from the Javascript SDK.
     *
     * @return string|null
     */
    public function getRawSignedRequestFromCookie()
    {
        if (isset($_COOKIE['fbsr_' . $this->app->getId()])) {
            return $_COOKIE['fbsr_' . $this->app->getId()];
        }

        return null;
    }
}
