<?php

namespace One23\GraphSdk;

use One23\GraphSdk\Authentication\AccessToken;
use One23\GraphSdk\Url;
use One23\GraphSdk\FileUpload\File;
use One23\GraphSdk\FileUpload\Video;
use One23\GraphSdk\Http\RequestBodyMultipart;
use One23\GraphSdk\Http\RequestBodyUrlEncoded;
use One23\GraphSdk\Exceptions\SDKException;

/**
 * Class Request

 */
class FacebookRequest
{
    /**
     * @var FacebookApp The Facebook app entity.
     */
    protected $app;

    /**
     * The access token to use for this request.
     */
    protected ?string $accessToken = null;

    /**
     * @var string The HTTP method for this request.
     */
    protected $method;

    /**
     * @var string The Graph endpoint for this request.
     */
    protected $endpoint;

    /**
     * @var array The headers to send with this request.
     */
    protected $headers = [];

    /**
     * @var array The parameters to send with this request.
     */
    protected $params = [];

    /**
     * @var array The files to send with this request.
     */
    protected $files = [];

    /**
     * @var string ETag to send with this request.
     */
    protected $eTag;

    /**
     * @var string Graph version to use for this request.
     */
    protected $graphVersion;

    public function __construct(
        FacebookApp $app = null,
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
     *
     * @param string $eTag
     */
    public function setETag($eTag)
    {
        $this->eTag = $eTag;
    }

    /**
     * Sets the access token with one harvested from a URL or POST params.
     *
     * @param string $accessToken The access token.
     *
     * @return FacebookRequest
     *
     * @throws SDKException
     */
    public function setAccessTokenFromParams($accessToken)
    {
        $existingAccessToken = $this->getAccessToken();
        if (!$existingAccessToken) {
            $this->setAccessToken($accessToken);
        } elseif ($accessToken !== $existingAccessToken) {
            throw new SDKException('Access token mismatch. The access token provided in the FacebookRequest and the one provided in the URL or POST params do not match.');
        }

        return $this;
    }

    /**
     * Return the access token for this request.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set the access token for this request.
     *
     * @param AccessToken|string|null
     *
     * @return FacebookRequest
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
        if ($accessToken instanceof AccessToken) {
            $this->accessToken = $accessToken->getValue();
        }

        return $this;
    }

    /**
     * Return the FacebookApp entity used for this request.
     *
     * @return FacebookApp
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Set the FacebookApp entity used for this request.
     *
     * @param FacebookApp|null $app
     */
    public function setApp(FacebookApp $app = null)
    {
        $this->app = $app;
    }

    /**
     * Validate that an access token exists for this request.
     *
     * @throws SDKException
     */
    public function validateAccessToken()
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new SDKException('You must provide an access token.');
        }
    }

    /**
     * Generate and return the headers for this request.
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = static::getDefaultHeaders();

        if ($this->eTag) {
            $headers['If-None-Match'] = $this->eTag;
        }

        return array_merge($this->headers, $headers);
    }

    /**
     * Set the headers for this request.
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * Return the default headers that every request should use.
     *
     * @return array
     */
    public static function getDefaultHeaders()
    {
        return [
            'User-Agent' => 'fb-php-' . Facebook::VERSION,
            'Accept-Encoding' => '*',
        ];
    }

    /**
     * Set the params for this request without filtering them first.
     *
     * @param array $params
     *
     * @return FacebookRequest
     */
    public function dangerouslySetParams(array $params = [])
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Iterate over the params and pull out the file uploads.
     *
     * @param array $params
     *
     * @return array
     */
    public function sanitizeFileParams(array $params)
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
    public function addFile(string $key, File $file)
    {
        $this->files[$key] = $file;
    }

    /**
     * Removes all the files from the upload queue.
     */
    public function resetFiles()
    {
        $this->files = [];
    }

    /**
     * Get the list of files to be uploaded.
     *
     * @return array
     */
    public function getFiles()
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
     *
     * @return array
     */
    public function getPostParams()
    {
        if ($this->getMethod() === 'POST') {
            return $this->getParams();
        }

        return [];
    }

    /**
     * Return the HTTP method for this request.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the HTTP method for this request.
     *
     * @param string
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Generate and return the params for this request.
     *
     * @return array
     */
    public function getParams()
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
     * @return FacebookRequest
     *
     * @throws SDKException
     */
    public function setParams(array $params = [])
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
     *
     * @return string|null
     */
    public function getAppSecretProof()
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
     *
     * @return RequestBodyUrlEncoded
     */
    public function getUrlEncodedBody()
    {
        $params = $this->getPostParams();

        return new RequestBodyUrlEncoded($params);
    }

    /**
     * The graph version used for this request.
     *
     * @return string
     */
    public function getGraphVersion()
    {
        return $this->graphVersion;
    }

    /**
     * Generate and return the URL for this request.
     *
     * @return string
     */
    public function getUrl()
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
    public function validateMethod()
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
     *
     * @return string
     */
    public function getEndpoint()
    {
        // For batch requests, this will be empty
        return $this->endpoint;
    }

    /**
     * Set the endpoint for this request.
     *
     * @param string
     *
     * @return FacebookRequest
     *
     * @throws SDKException
     */
    public function setEndpoint($endpoint)
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
