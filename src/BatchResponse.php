<?php

namespace One23\GraphSdk;

use ArrayIterator;
use IteratorAggregate;
use ArrayAccess;

class BatchResponse extends Response implements IteratorAggregate, ArrayAccess
{
    /**
     * An array of Response entities.
     */
    protected array $responses = [];

    public function __construct(
        protected FacebookBatchRequest $batchRequest,
        Response $response
    ) {
        $request = $response->getRequest();
        $body = $response->getBody();
        $httpStatusCode = $response->getHttpStatusCode();
        $headers = $response->getHeaders();
        parent::__construct($request, $body, $httpStatusCode, $headers);

        $responses = $response->getDecodedBody();
        $this->setResponses($responses);
    }

    /**
     * Returns an array of Response entities.
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * The main batch response will be an array of requests so
     * we need to iterate over all the responses.
     */
    public function setResponses(array $responses): void
    {
        $this->responses = [];

        foreach ($responses as $key => $graphResponse) {
            $this->addResponse($key, $graphResponse);
        }
    }

    public function getIterator(): \ArrayIterator
    {
        return new ArrayIterator($this->responses);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->addResponse(
            (int)$offset,
            (array)$value
        );
    }

    /**
     * Add a response to the list.
     */
    public function addResponse(int $key, array $response = null): void
    {
        $originalRequestName = $this->batchRequest[$key]['name'] ?? $key;
        $originalRequest     = $this->batchRequest[$key]['request'] ?? null;

        $httpResponseBody = $response['body'] ?? null;
        $httpResponseCode = $response['code'] ?? null;
        $httpResponseHeaders = isset($response['headers']) ? $this->normalizeBatchHeaders($response['headers']) : [];

        $this->responses[$originalRequestName] = new Response(
            $originalRequest,
            $httpResponseBody,
            $httpResponseCode,
            $httpResponseHeaders
        );
    }

    /**
     * Converts the batch header array into a standard format.
     */
    private function normalizeBatchHeaders(array $batchHeaders): array
    {
        return array_column(
            $batchHeaders, 'value', 'name'
        );
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->responses[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->responses[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->responses[$offset] ?? null;
    }
}
