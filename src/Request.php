<?php

namespace One23\GraphSdk;

use One23\GraphSdk\Authentication\AccessToken;
use One23\GraphSdk\Url;
use One23\GraphSdk\FileUpload\File;
use One23\GraphSdk\FileUpload\Video;
use One23\GraphSdk\Http\RequestBodyMultipart;
use One23\GraphSdk\Http\RequestBodyUrlEncoded;
use One23\GraphSdk\Exceptions\SDKException;

class Request
{
    /**
     * The Facebook app entity.
     */
    protected App $app;

    /**
     * The access token to use for this request.
     */
    protected ?string $accessToken = null;

    /**
     * The HTTP method for this request.
     */
    protected string $method;

    /**
     * The Graph endpoint for this request.
     */
    protected string $endpoint;

    /**
     * The headers to send with this request.
     */
    protected array $headers = [];

    /**
     * The parameters to send with this request.
     */
    protected array $params = [];

    /**
     * The files to send with this request.
     */
    protected array $files = [];

    /**
     * ETag to send with this request.
     */
    protected string $eTag;

    /**
     * Graph version to use for this request.
     */
    protected string $graphVersion;

    /**
     * @throws SDKException
     */
    public function __construct(
        App $app = null,
        string $accessToken = null,
        string $method = null,
        string $endpoint = null,
        array $params = [],
        string $eTag = null,
        string $graphVersion = null
    ) {
        $this->setApp($app);
        $this->setAccessToken($accessToken);
        $this->setMethod($method);
        $this->setEndpoint($endpoint);
        $this->setParams($params);
        $this->setETag($eTag);
        $this->graphVersion = $graphVersion ?: Facebook::DEFAULT_GRAPH_VERSION;
    }

    /**
     * Sets the eTag value.
     */
    public function setETag(string $eTag): static
    {
        $this->eTag = $eTag;

        return $this;
    }

    /**
     * Sets the access token with one harvested from a URL or POST params.
     *
     * @throws SDKException
     */
    public function setAccessTokenFromParams(string $accessToken): static
    {
        $existingAccessToken = $this->getAccessToken();
        if (!$existingAccessToken) {
            $this->setAccessToken($accessToken);
        }
        elseif ($accessToken !== $existingAccessToken) {
            throw new SDKException('Access token mismatch. The access token provided in the Request and the one provided in the URL or POST params do not match.');
        }

        return $this;
    }

    /**
     * Return the access token for this request.
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Set the access token for this request.
     */
    public function setAccessToken(AccessToken|string $accessToken = null): static
    {
        if ($accessToken instanceof AccessToken) {
            $this->accessToken = $accessToken->getValue();
        }
        else {
            $this->accessToken = $accessToken;
        }

        return $this;
    }

    /**
     * Return the App entity used for this request.
     */
    public function getApp(): App
    {
        return $this->app;
    }

    /**
     * Set the App entity used for this request.
     */
    public function setApp(App $app = null): static
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Validate that an access token exists for this request.
     *
     * @throws SDKException
     */
    public function validateAccessToken(): void
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new SDKException('You must provide an access token.');
        }
    }

    /**
     * Generate and return the headers for this request.
     */
    public function getHeaders(): array
    {
        $headers = static::getDefaultHeaders();

        if ($this->eTag) {
            $headers['If-None-Match'] = $this->eTag;
        }

        return array_merge($this->headers, $headers);
    }

    /**
     * Set the headers for this request.
     */
    public function setHeaders(array $headers): static
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Return the default headers that every request should use.
     */
    public static function getDefaultHeaders(): array
    {
        return [
            'User-Agent' => 'php-8-graph-sdk-' . Facebook::VERSION,
            'Accept-Encoding' => '*',
        ];
    }

    /**
     * Set the params for this request without filtering them first.
     */
    public function dangerouslySetParams(array $params = []): static
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Iterate over the params and pull out the file uploads.
     */
    public function sanitizeFileParams(array $params): array
    {
        foreach ($params as $key => $value) {
            if ($value instanceof File) {
                $this->addFile($key, $value);
                unset($params[$key]);
            }
        }

        return $params;
    }

    /**
     * Add a file to be uploaded.
     */
    public function addFile(string $key, File $file): static
    {
        $this->files[$key] = $file;

        return $this;
    }

    /**
     * Removes all the files from the upload queue.
     */
    public function resetFiles(): static
    {
        $this->files = [];

        return $this;
    }

    /**
     * Get the list of files to be uploaded.
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Let's us know if there is a file upload with this request.
     */
    public function containsFileUploads(): bool
    {
        return !empty($this->files);
    }

    /**
     * Let's us know if there is a video upload with this request.
     */
    public function containsVideoUploads(): bool
    {
        foreach ($this->files as $file) {
            if ($file instanceof Video) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the body of the request as multipart/form-data.
     */
    public function getMultipartBody(): RequestBodyMultipart
    {
        $params = $this->getPostParams();

        return new RequestBodyMultipart($params, $this->files);
    }

    /**
     * Only return params on POST requests.
     */
    public function getPostParams(): array
    {
        if ($this->getMethod() === 'POST') {
            return $this->getParams();
        }

        return [];
    }

    /**
     * Return the HTTP method for this request.
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set the HTTP method for this request.
     */
    public function setMethod(string $method): static
    {
        $this->method = strtoupper($method);

        return $this;
    }

    /**
     * Generate and return the params for this request.
     */
    public function getParams(): array
    {
        $params = $this->params;

        $accessToken = $this->getAccessToken();
        if ($accessToken) {
            $params['access_token'] = $accessToken;
            $params['appsecret_proof'] = $this->getAppSecretProof();
        }

        return $params;
    }

    /**
     * Set the params for this request.
     *
     * @throws SDKException
     */
    public function setParams(array $params = []): static
    {
        if (isset($params['access_token'])) {
            $this->setAccessTokenFromParams($params['access_token']);
        }

        // Don't let these buggers slip in.
        unset($params['access_token'], $params['appsecret_proof']);

        // @TODO Refactor code above with this
        //$params = $this->sanitizeAuthenticationParams($params);
        $params = $this->sanitizeFileParams($params);
        $this->dangerouslySetParams($params);

        return $this;
    }

    /**
     * Generate an app secret proof to sign this request.
     */
    public function getAppSecretProof(): ?string
    {
        if (!$accessTokenEntity = $this->getAccessTokenEntity()) {
            return null;
        }

        return $accessTokenEntity->getAppSecretProof($this->app->getSecret());
    }

    /**
     * Return the access token for this request as an AccessToken entity.
     */
    public function getAccessTokenEntity(): ?AccessToken
    {
        return $this->accessToken
            ? new AccessToken($this->accessToken)
            : null;
    }

    /**
     * Returns the body of the request as URL-encoded.
     */
    public function getUrlEncodedBody(): RequestBodyUrlEncoded
    {
        $params = $this->getPostParams();

        return new RequestBodyUrlEncoded($params);
    }

    /**
     * The graph version used for this request.
     */
    public function getGraphVersion(): string
    {
        return $this->graphVersion;
    }

    /**
     * Generate and return the URL for this request.
     */
    public function getUrl(): string
    {
        $this->validateMethod();

        $graphVersion = Url\Manipulator::forceSlashPrefix($this->graphVersion);
        $endpoint = Url\Manipulator::forceSlashPrefix($this->getEndpoint());

        $url = $graphVersion . $endpoint;

        if ($this->getMethod() !== 'POST') {
            $params = $this->getParams();
            $url = Url\Manipulator::appendParamsToUrl($url, $params);
        }

        return $url;
    }

    /**
     * Validate that the HTTP method is set.
     *
     * @throws SDKException
     */
    public function validateMethod(): void
    {
        if (!$this->method) {
            throw new SDKException('HTTP method not specified.');
        }

        if (!in_array($this->method, ['GET', 'POST', 'DELETE'])) {
            throw new SDKException('Invalid HTTP method specified.');
        }
    }

    /**
     * Return the endpoint for this request.
     */
    public function getEndpoint(): string
    {
        // For batch requests, this will be empty
        return $this->endpoint;
    }

    /**
     * Set the endpoint for this request.
     *
     * @throws SDKException
     */
    public function setEndpoint(string $endpoint): static
    {
        // Harvest the access token from the endpoint to keep things in sync
        $params = Url\Manipulator::getParamsAsArray($endpoint);
        if (isset($params['access_token'])) {
            $this->setAccessTokenFromParams($params['access_token']);
        }

        // Clean the token & app secret proof from the endpoint.
        $filterParams = ['access_token', 'appsecret_proof'];
        $this->endpoint = Url\Manipulator::removeParamsFromUrl($endpoint, $filterParams);

        return $this;
    }
}
