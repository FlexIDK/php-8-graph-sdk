<?php

namespace One23\GraphSdk\Authentication;

use One23\GraphSdk\Facebook;
use One23\GraphSdk\FacebookApp;
use One23\GraphSdk\FacebookRequest;
use One23\GraphSdk\Response;
use One23\GraphSdk\FacebookClient;
use One23\GraphSdk\Exceptions\ResponseException;
use One23\GraphSdk\Exceptions\SDKException;

class OAuth2Client
{
    /**
     * The base authorization URL.
     */
    const BASE_AUTHORIZATION_URL = 'https://www.facebook.com';

    /**
     * The version of the Graph API to use.
     */
    protected string $graphVersion;

    /**
     * The last request sent to Graph.
     */
    protected ?FacebookRequest $lastRequest = null;

    public function __construct(protected FacebookApp $app, protected FacebookClient $client, string $graphVersion = null)
    {
        $this->graphVersion = $graphVersion ?: Facebook::DEFAULT_GRAPH_VERSION;
    }

    /**
     * Returns the last FacebookRequest that was sent.
     * Useful for debugging and testing.
     */
    public function getLastRequest(): ?FacebookRequest
    {
        return $this->lastRequest;
    }

    /**
     * Get the metadata associated with the access token.
     *
     * @throws SDKException
     */
    public function debugToken(AccessToken|string $accessToken): AccessTokenMetadata
    {
        $accessToken = $accessToken instanceof AccessToken ? $accessToken->getValue() : $accessToken;
        $params = ['input_token' => $accessToken];

        $this->lastRequest = new FacebookRequest(
            $this->app,
            $this->app->getAccessToken(),
            'GET',
            '/debug_token',
            $params,
            null,
            $this->graphVersion
        );
        $response = $this->client->sendRequest($this->lastRequest);
        $metadata = $response->getDecodedBody();

        return new AccessTokenMetadata($metadata);
    }

    /**
     * Generates an authorization URL to begin the process of authenticating a user.
     */
    public function getAuthorizationUrl(
        string $redirectUrl,
        string $state,
        array $scope = [],
        array $params = [],
        string $separator = '&'
    ): string
    {
        $params = [
            'client_id' => $this->app->getId(),
            'state' => $state,
            'response_type' => 'code',
            'sdk' => 'php-8-graph-sdk-' . Facebook::VERSION,
            'redirect_uri' => $redirectUrl,
            'scope' => implode(',', $scope),

            ...$params,
        ];

        return static::BASE_AUTHORIZATION_URL . '/' . $this->graphVersion . '/dialog/oauth?' . http_build_query($params, null, $separator);
    }

    /**
     * Get a valid access token from a code.
     *
     * @throws SDKException
     */
    public function getAccessTokenFromCode(string $code, string $redirectUri = ''): AccessToken
    {
        $params = [
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ];

        return $this->requestAnAccessToken($params);
    }

    /**
     * Send a request to the OAuth endpoint.
     *
     * @throws SDKException
     */
    protected function requestAnAccessToken(array $params): AccessToken
    {
        $response = $this->sendRequestWithClientParams('/oauth/access_token', $params);
        $data = $response->getDecodedBody();

        if (!isset($data['access_token'])) {
            throw new SDKException('Access token was not returned from Graph.', 401);
        }

        // Graph returns two different key names for expiration time
        // on the same endpoint. Doh! :/
        $expiresAt = 0;
        if (isset($data['expires'])) {
            // For exchanging a short lived token with a long lived token.
            // The expiration time in seconds will be returned as "expires".
            $expiresAt = time() + $data['expires'];
        }
        elseif (isset($data['expires_in'])) {
            // For exchanging a code for a short lived access token.
            // The expiration time in seconds will be returned as "expires_in".
            // See: https://developers.facebook.com/docs/facebook-login/access-tokens#long-via-code
            $expiresAt = time() + $data['expires_in'];
        }

        return new AccessToken(
            (string)$data['access_token'],
            (int)$expiresAt
        );
    }

    /**
     * Send a request to Graph with an app access token.
     *
     * @throws ResponseException|SDKException
     */
    protected function sendRequestWithClientParams(
        string $endpoint,
        array $params,
        AccessToken|string $accessToken = null
    ): Response
    {
        $params += $this->getClientParams();

        $accessToken = $accessToken ?: $this->app->getAccessToken();

        $this->lastRequest = new FacebookRequest(
            $this->app,
            $accessToken,
            'GET',
            $endpoint,
            $params,
            null,
            $this->graphVersion
        );

        return $this->client->sendRequest($this->lastRequest);
    }

    /**
     * Returns the client_* params for OAuth requests.
     */
    protected function getClientParams(): array
    {
        return [
            'client_id' => $this->app->getId(),
            'client_secret' => $this->app->getSecret(),
        ];
    }

    /**
     * Exchanges a short-lived access token with a long-lived access token.
     *
     * @throws SDKException
     */
    public function getLongLivedAccessToken(AccessToken|string $accessToken): AccessToken
    {
        $accessToken = $accessToken instanceof AccessToken ? $accessToken->getValue() : $accessToken;
        $params = [
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $accessToken,
        ];

        return $this->requestAnAccessToken($params);
    }

    /**
     * Get a valid code from an access token.
     *
     * @throws SDKException
     */
    public function getCodeFromLongLivedAccessToken(
        AccessToken|string $accessToken,
        string $redirectUri = ''
    ): AccessToken
    {
        $params = [
            'redirect_uri' => $redirectUri,
        ];

        $response = $this->sendRequestWithClientParams('/oauth/client_code', $params, $accessToken);
        $data = $response->getDecodedBody();

        if (!isset($data['code'])) {
            throw new SDKException('Code was not returned from Graph.', 401);
        }

        return $data['code'];
    }
}
