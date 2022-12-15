<?php

namespace One23\GraphSdk;

use One23\GraphSdk\HttpClients\Clients\ClientInterface;
use One23\GraphSdk\HttpClients\Clients\Curl;
use One23\GraphSdk\HttpClients\Clients\Stream;
use One23\GraphSdk\Exceptions\SDKException;

class Client
{
    /**
     * Production Graph API URL.
     */
    const BASE_GRAPH_URL = 'https://graph.facebook.com';

    /**
     * Graph API URL for video uploads.
     */
    const BASE_GRAPH_VIDEO_URL = 'https://graph-video.facebook.com';

    /**
     * Beta Graph API URL.
     */
    const BASE_GRAPH_URL_BETA = 'https://graph.beta.facebook.com';

    /**
     * Beta Graph API URL for video uploads.
     */
    const BASE_GRAPH_VIDEO_URL_BETA = 'https://graph-video.beta.facebook.com';

    /**
     * The timeout in seconds for a normal request.
     */
    const DEFAULT_REQUEST_TIMEOUT = 60;

    /**
     * The timeout in seconds for a request that contains file uploads.
     */
    const DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT = 3600;

    /**
     * The timeout in seconds for a request that contains video uploads.
     */
    const DEFAULT_VIDEO_UPLOAD_REQUEST_TIMEOUT = 7200;

    /**
     * The number of calls that have been made to Graph.
     */
    public static int $requestCount = 0;

    /**
     * HTTP client handler.
     */
    protected ClientInterface $httpClientHandler;

    public function __construct(
        ClientInterface $httpClientHandler = null,
        protected bool $enableBetaMode = false
    ) {
        $this->httpClientHandler = $httpClientHandler ?: $this->detectHttpClientHandler();
    }

    /**
     * Detects which HTTP client handler to use.
     */
    public function detectHttpClientHandler(): ClientInterface
    {
        return extension_loaded('curl')
            ? new Curl()
            : new Stream();
    }

    /**
     * Returns the HTTP client handler.
     */
    public function getHttpClientHandler(): ClientInterface
    {
        return $this->httpClientHandler;
    }

    /**
     * Sets the HTTP client handler.
     */
    public function setHttpClientHandler(ClientInterface $httpClientHandler): static
    {
        $this->httpClientHandler = $httpClientHandler;

        return $this;
    }

    /**
     * Toggle beta mode.
     */
    public function enableBetaMode(bool $enableBetaMode = true): static
    {
        $this->enableBetaMode = $enableBetaMode;

        return $this;
    }

    /**
     * Makes a batched request to Graph and returns the result.
     *
     * @throws SDKException
     */
    public function sendBatchRequest(BatchRequest $request): BatchResponse
    {
        $request->prepareRequestsForBatch();
        $facebookResponse = $this->sendRequest($request);

        return new BatchResponse($request, $facebookResponse);
    }

    /**
     * Makes the request to Graph and returns the result.
     *
     * @throws SDKException
     */
    public function sendRequest(Request $request): Response
    {
        if (get_class($request) === Request::class) {
            $request->validateAccessToken();
        }

        list($url, $method, $headers, $body) = $this->prepareRequestMessage($request);

        // Since file uploads can take a while, we need to give more time for uploads
        $timeOut = static::DEFAULT_REQUEST_TIMEOUT;
        if ($request->containsFileUploads()) {
            $timeOut = static::DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT;
        }
        elseif ($request->containsVideoUploads()) {
            $timeOut = static::DEFAULT_VIDEO_UPLOAD_REQUEST_TIMEOUT;
        }

        // Should throw `SDKException` exception on HTTP client error.
        // Don't catch to allow it to bubble up.
        $rawResponse = $this->httpClientHandler->send($url, $method, $body, $headers, $timeOut);

        static::$requestCount++;

        $returnResponse = new Response(
            $request,
            $rawResponse->getBody(),
            $rawResponse->getHttpResponseCode(),
            $rawResponse->getHeaders()
        );

        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }

        return $returnResponse;
    }

    /**
     * Prepares the request for sending to the client handler.
     */
    public function prepareRequestMessage(Request $request): array
    {
        $postToVideoUrl = $request->containsVideoUploads();
        $url = $this->getBaseGraphUrl($postToVideoUrl) . $request->getUrl();

        // If we're sending files they should be sent as multipart/form-data
        if ($request->containsFileUploads()) {
            $requestBody = $request->getMultipartBody();
            $request->setHeaders([
                'Content-Type' => 'multipart/form-data; boundary=' . $requestBody->getBoundary(),
            ]);
        }
        else {
            $requestBody = $request->getUrlEncodedBody();
            $request->setHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ]);
        }

        return [
            $url,
            $request->getMethod(),
            $request->getHeaders(),
            $requestBody->getBody(),
        ];
    }

    /**
     * Returns the base Graph URL.
     */
    public function getBaseGraphUrl(bool $postToVideoUrl = false): string
    {
        if ($postToVideoUrl) {
            return $this->enableBetaMode ? static::BASE_GRAPH_VIDEO_URL_BETA : static::BASE_GRAPH_VIDEO_URL;
        }

        return $this->enableBetaMode ? static::BASE_GRAPH_URL_BETA : static::BASE_GRAPH_URL;
    }
}
