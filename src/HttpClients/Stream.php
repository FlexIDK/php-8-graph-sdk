<?php

namespace One23\GraphSdk\HttpClients;

/**
 * Abstraction for the procedural stream elements so that the functions can be
 * mocked and the implementation can be tested.
 */
class Stream
{
    /**
     * @var resource Context stream resource instance
     */
    protected $stream;

    /**
     * Response headers from the stream wrapper
     */
    protected array $responseHeaders = [];

    /**
     * Make a new context stream reference instance
     */
    public function streamContextCreate(array $options): void
    {
        $this->stream = stream_context_create($options);
    }

    /**
     * The response headers from the stream wrapper
     */
    public function getResponseHeaders(): array
    {
        return $this->responseHeaders;
    }

    /**
     * Send a stream wrapped request
     */
    public function fileGetContents(string $url): mixed
    {
        $rawResponse = file_get_contents($url, false, $this->stream);
        $this->responseHeaders = $http_response_header ?: [];

        return $rawResponse;
    }
}

