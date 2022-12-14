<?php

namespace One23\GraphSdk;

use InvalidArgumentException;
use One23\GraphSdk\Authentication\AccessToken;
use One23\GraphSdk\Authentication\OAuth2Client;
use One23\GraphSdk\FileUpload\File;
use One23\GraphSdk\FileUpload\ResumableUploader;
use One23\GraphSdk\FileUpload\TransferChunk;
use One23\GraphSdk\FileUpload\Video;
use One23\GraphSdk\GraphNodes\GraphEdge;
use One23\GraphSdk\Url;
use One23\GraphSdk\PseudoRandomString\GeneratorFactory;
use One23\GraphSdk\PseudoRandomString\Generators\GeneratorInterface;
use One23\GraphSdk\HttpClients\HttpClientsFactory;
use One23\GraphSdk\PersistentData\PersistentDataFactory;
use One23\GraphSdk\PersistentData\PersistentDataInterface;
use One23\GraphSdk\Helpers\FacebookCanvasHelper;
use One23\GraphSdk\Helpers\FacebookJavaScriptHelper;
use One23\GraphSdk\Helpers\FacebookPageTabHelper;
use One23\GraphSdk\Helpers\FacebookRedirectLoginHelper;
use One23\GraphSdk\Exceptions\SDKException;

class Facebook
{
    const VERSION = '1.0.0';
    const DEFAULT_GRAPH_VERSION = 'v15.0';

    const APP_ID_ENV_NAME = 'FACEBOOK_APP_ID';
    const APP_SECRET_ENV_NAME = 'FACEBOOK_APP_SECRET';
    const APP_GRAPH_VERSION = 'FACEBOOK_APP_GRAPH_VERSION';

    /**
     * The FacebookApp entity.
     */
    protected FacebookApp $app;

    /**
     * @The Facebook client service.
     */
    protected FacebookClient $client;

    /**
     * The OAuth 2.0 client service.
     */
    protected OAuth2Client $oAuth2Client;

    /**
     * The URL detection handler.
     */
    protected ?Url\DetectionInterface $urlDetectionHandler;

    /**
     * The cryptographically secure pseudo-random string generator.
     */
    protected ?GeneratorInterface $pseudoRandomStringGenerator;

    /**
     * The default access token to use with requests.
     */
    protected ?AccessToken $defaultAccessToken;

    /**
     * The default Graph version we want to use.
     */
    protected ?string $defaultGraphVersion = null;

    /**
     * The persistent data handler.
     */
    protected ?PersistentDataInterface $persistentDataHandler = null;

    /**
     * Stores the last request made to Graph.
     */
    protected Response|BatchResponse|null $lastResponse = null;

    /**
     * @throws SDKException
     */
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'app_id' => getenv(static::APP_ID_ENV_NAME),
            'app_secret' => getenv(static::APP_SECRET_ENV_NAME),
            'default_graph_version' => getenv(static::APP_GRAPH_VERSION),
            'enable_beta_mode' => false,
            'http_client_handler' => null,
            'persistent_data_handler' => null,
            'pseudo_random_string_generator' => null,
            'url_detection_handler' => null,
        ], $config);

        if (!$config['app_id']) {
            throw new SDKException('Required "app_id" key not supplied in config and could not find fallback environment variable "' . static::APP_ID_ENV_NAME . '"');
        }
        if (!$config['app_secret']) {
            throw new SDKException('Required "app_secret" key not supplied in config and could not find fallback environment variable "' . static::APP_SECRET_ENV_NAME . '"');
        }
        if (!$config['default_graph_version']) {
            throw new SDKException('Required "default_graph_version" key not supplied in config and could not find fallback environment variable "' . static::APP_GRAPH_VERSION . '"');
        }

        $this->app = new FacebookApp($config['app_id'], $config['app_secret']);
        $this->client = new FacebookClient(
            HttpClientsFactory::createHttpClient($config['http_client_handler']),
            $config['enable_beta_mode']
        );
        $this->pseudoRandomStringGenerator = GeneratorFactory::createPseudoRandomStringGenerator(
            $config['pseudo_random_string_generator']
        );
        $this->setUrlDetectionHandler($config['url_detection_handler'] ?: new Url\DetectionHandler());
        $this->persistentDataHandler = PersistentDataFactory::createPersistentDataHandler(
            $config['persistent_data_handler']
        );

        if (isset($config['default_access_token'])) {
            $this->setDefaultAccessToken($config['default_access_token']);
        }

        $this->defaultGraphVersion = $config['default_graph_version'];
    }

    /**
     * Changes the URL detection handler.
     */
    private function setUrlDetectionHandler(Url\DetectionInterface $urlDetectionHandler): void
    {
        $this->urlDetectionHandler = $urlDetectionHandler;
    }

    /**
     * Returns the last response returned from Graph.
     */
    public function getLastResponse(): Response|BatchResponse|null
    {
        return $this->lastResponse;
    }

    /**
     * Returns the URL detection handler.
     */
    public function getUrlDetectionHandler(): Url\DetectionInterface
    {
        return $this->urlDetectionHandler;
    }

    /**
     * Returns the default AccessToken entity.
     */
    public function getDefaultAccessToken(): ?AccessToken
    {
        return $this->defaultAccessToken;
    }

    /**
     * Sets the default access token to use with requests.
     *
     * @throws InvalidArgumentException
     */
    public function setDefaultAccessToken(AccessToken|string $accessToken): void
    {
        if (is_string($accessToken)) {
            $this->defaultAccessToken = new AccessToken($accessToken);

            return;
        }

        if ($accessToken instanceof AccessToken) {
            $this->defaultAccessToken = $accessToken;

            return;
        }

        throw new InvalidArgumentException('The default access token must be of type "string" or Facebook\AccessToken');
    }

    /**
     * Returns the default Graph version.
     */
    public function getDefaultGraphVersion(): string
    {
        return $this->defaultGraphVersion;
    }

    /**
     * Returns the redirect login helper.
     */
    public function getRedirectLoginHelper(): FacebookRedirectLoginHelper
    {
        return new FacebookRedirectLoginHelper(
            $this->getOAuth2Client(),
            $this->persistentDataHandler,
            $this->urlDetectionHandler,
            $this->pseudoRandomStringGenerator
        );
    }

    /**
     * Returns the OAuth 2.0 client service.
     */
    public function getOAuth2Client(): OAuth2Client
    {
        if (!isset($this->oAuth2Client)) {
            $app = $this->getApp();
            $client = $this->getClient();
            $this->oAuth2Client = new OAuth2Client($app, $client, $this->defaultGraphVersion);
        }

        return $this->oAuth2Client;
    }

    /**
     * Returns the FacebookApp entity.
     */
    public function getApp(): FacebookApp
    {
        return $this->app;
    }

    /**
     * Returns the FacebookClient service.
     */
    public function getClient(): FacebookClient
    {
        return $this->client;
    }

    /**
     * Returns the JavaScript helper.
     */
    public function getJavaScriptHelper(): FacebookJavaScriptHelper
    {
        return new FacebookJavaScriptHelper($this->app, $this->client, $this->defaultGraphVersion);
    }

    /**
     * Returns the canvas helper.
     */
    public function getCanvasHelper(): FacebookCanvasHelper
    {
        return new FacebookCanvasHelper($this->app, $this->client, $this->defaultGraphVersion);
    }

    /**
     * Returns the page tab helper.
     */
    public function getPageTabHelper(): FacebookPageTabHelper
    {
        return new FacebookPageTabHelper($this->app, $this->client, $this->defaultGraphVersion);
    }

    /**
     * Sends a GET request to Graph and returns the result.
     *
     * @throws SDKException
     */
    public function get(
        string $endpoint,
        AccessToken|string $accessToken = null,
        string $eTag = null,
        string $graphVersion = null
    ): Response
    {
        return $this->sendRequest(
            'GET',
            $endpoint,
            [],
            $accessToken,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Sends a request to Graph and returns the result.
     *
     * @throws SDKException
     */
    public function sendRequest(
        string $method,
        string $endpoint,
        array $params = [],
        AccessToken|string $accessToken = null,
        string $eTag = null,
        string $graphVersion = null
    ): Response
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;
        $request = $this->request($method, $endpoint, $params, $accessToken, $eTag, $graphVersion);

        return $this->lastResponse = $this->client->sendRequest($request);
    }

    /**
     * Instantiates a new FacebookRequest entity.
     */
    public function request(
        string $method,
        string $endpoint,
        array $params = [],
        AccessToken|string $accessToken = null,
        string $eTag = null,
        string $graphVersion = null
    ): FacebookRequest
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;

        return new FacebookRequest(
            $this->app,
            $accessToken,
            $method,
            $endpoint,
            $params,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Sends a POST request to Graph and returns the result.
     *
     * @throws SDKException
     */
    public function post(
        string $endpoint,
        array $params = [],
        AccessToken|string $accessToken = null,
        string $eTag = null,
        string $graphVersion = null
    ): Response
    {
        return $this->sendRequest(
            'POST',
            $endpoint,
            $params,
            $accessToken,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Sends a DELETE request to Graph and returns the result.
     *
     * @throws SDKException
     */
    public function delete(
        string $endpoint,
        array $params = [],
        AccessToken|string $accessToken = null,
        string $eTag = null,
        string $graphVersion = null
    ): Response
    {
        return $this->sendRequest(
            'DELETE',
            $endpoint,
            $params,
            $accessToken,
            $eTag,
            $graphVersion
        );
    }

    /**
     * Sends a request to Graph for the next page of results.
     *
     * @throws SDKException
     */
    public function next(GraphEdge $graphEdge): ?GraphEdge
    {
        return $this->getPaginationResults($graphEdge, 'next');
    }

    /**
     * Sends a request to Graph for the next page of results.
     *
     * @throws SDKException
     */
    public function getPaginationResults(GraphEdge $graphEdge, string $direction): ?GraphEdge
    {
        $paginationRequest = $graphEdge->getPaginationRequest($direction);
        if (!$paginationRequest) {
            return null;
        }

        $this->lastResponse = $this->client->sendRequest($paginationRequest);

        // Keep the same GraphNode subclass
        $subClassName = $graphEdge->getSubClassName();
        $graphEdge = $this->lastResponse->getGraphEdge($subClassName, false);

        return count($graphEdge) > 0 ? $graphEdge : null;
    }

    /**
     * Sends a request to Graph for the previous page of results.
     *
     * @throws SDKException
     */
    public function previous(GraphEdge $graphEdge): ?GraphEdge
    {
        return $this->getPaginationResults($graphEdge, 'previous');
    }

    /**
     * Sends a batched request to Graph and returns the result.
     *
     * @throws SDKException
     */
    public function sendBatchRequest(
        array $requests,
        AccessToken|string $accessToken = null,
        string $graphVersion = null
    ): BatchResponse
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;
        $batchRequest = new FacebookBatchRequest(
            $this->app,
            $requests,
            $accessToken,
            $graphVersion
        );

        return $this->lastResponse = $this->client->sendBatchRequest($batchRequest);
    }

    /**
     * Instantiates an empty FacebookBatchRequest entity.
     */
    public function newBatchRequest(
        AccessToken|string $accessToken = null,
        string $graphVersion = null
    ): FacebookBatchRequest
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;

        return new FacebookBatchRequest(
            $this->app,
            [],
            $accessToken,
            $graphVersion
        );
    }

    /**
     * Factory to create FacebookFile's.
     *
     * @throws SDKException
     */
    public function fileToUpload(string $pathToFile): File
    {
        return new File($pathToFile);
    }

    /**
     * Upload a video in chunks.
     *
     * @throws SDKException
     */
    public function uploadVideo(
        int $target,
        string $pathToFile,
        array $metadata = [],
        string $accessToken = null,
        int $maxTransferTries = 5,
        string $graphVersion = null
    ): array
    {
        $accessToken = $accessToken ?: $this->defaultAccessToken;
        $graphVersion = $graphVersion ?: $this->defaultGraphVersion;

        $uploader = new ResumableUploader($this->app, $this->client, $accessToken, $graphVersion);
        $endpoint = '/'.$target.'/videos';
        $file = $this->videoToUpload($pathToFile);
        $chunk = $uploader->start($endpoint, $file);

        do {
            $chunk = $this->maxTriesTransfer($uploader, $endpoint, $chunk, $maxTransferTries);
        } while (!$chunk->isLastChunk());

        return [
          'video_id' => $chunk->getVideoId(),
          'success' => $uploader->finish($endpoint, $chunk->getUploadSessionId(), $metadata),
        ];
    }

    /**
     * Factory to create FacebookVideo's.
     *
     * @throws SDKException
     */
    public function videoToUpload(string $pathToFile): Video
    {
        return new Video($pathToFile);
    }

    /**
     * Attempts to upload a chunk of a file in $retryCountdown tries.
     *
     * @throws SDKException
     */
    private function maxTriesTransfer(
        ResumableUploader $uploader,
        string $endpoint,
        TransferChunk $chunk,
        int $retryCountdown
    ): TransferChunk
    {
        $newChunk = $uploader->transfer($endpoint, $chunk, $retryCountdown < 1);

        if ($newChunk !== $chunk) {
            return $newChunk;
        }

        $retryCountdown--;

        // If transfer() returned the same chunk entity, the transfer failed but is resumable.
        return $this->maxTriesTransfer($uploader, $endpoint, $chunk, $retryCountdown);
    }
}
