<?php

namespace One23\GraphSdk;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use ArrayAccess;
use One23\GraphSdk\Authentication\AccessToken;
use One23\GraphSdk\Exceptions\SDKException;

class BatchRequest extends Request implements IteratorAggregate, ArrayAccess
{
    /**
     * An array of Request entities to send.
     */
    protected array $requests = [];

    /**
     * An array of files to upload.
     */
    protected array $attachedFiles;

    /**
     * @throws SDKException
     */
    public function __construct(
        App $app = null,
        array $requests = [],
        AccessToken|string $accessToken = null,
        string $graphVersion = null
    )
    {
        parent::__construct($app, $accessToken, 'POST', '', [], null, $graphVersion);

        $this->add($requests);
    }

    /**
     * Adds a new request to the array.
     *
     * @throws InvalidArgumentException|SDKException
     */
    public function add(Request|array $request, string|array $options = null): BatchRequest
    {
        if (is_array($request)) {
            foreach ($request as $key => $req) {
                $this->add($req, $key);
            }

            return $this;
        }

        if (!$request instanceof Request) {
            throw new InvalidArgumentException('Argument for add() must be of type array or Request.');
        }

        if (is_null($options)) {
            $options = [];
        }
        elseif (!is_array($options)) {
            $options = ['name' => $options];
        }

        $this->addFallbackDefaults($request);

        // File uploads
        $attachedFiles = $this->extractFileAttachments($request);

        $name = $options['name'] ?? null;

        unset($options['name']);

        $requestToAdd = [
            'name' => $name,
            'request' => $request,
            'options' => $options,
            'attached_files' => $attachedFiles,
        ];

        $this->requests[] = $requestToAdd;

        return $this;
    }

    /**
     * Ensures that the App and access token fall back when missing.
     *
     * @throws SDKException
     */
    public function addFallbackDefaults(Request $request): void
    {
        if (!$request->getApp()) {
            $app = $this->getApp();
            if (!$app) {
                throw new SDKException('Missing App on Request and no fallback detected on BatchRequest.');
            }
            $request->setApp($app);
        }

        if (!$request->getAccessToken()) {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                throw new SDKException('Missing access token on Request and no fallback detected on BatchRequest.');
            }
            $request->setAccessToken($accessToken);
        }
    }

    /**
     * Extracts the files from a request.
     *
     * @throws SDKException
     */
    public function extractFileAttachments(Request $request): ?string
    {
        if (!$request->containsFileUploads()) {
            return null;
        }

        $files = $request->getFiles();
        $fileNames = [];
        foreach ($files as $file) {
            $fileName = uniqid();
            $this->addFile($fileName, $file);
            $fileNames[] = $fileName;
        }

        $request->resetFiles();

        // @TODO Does Graph support multiple uploads on one endpoint?
        return implode(',', $fileNames);
    }

    /**
     * Return the Request entities.
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * Prepares the requests to be sent as a batch request.
     *
     * @throws SDKException
     */
    public function prepareRequestsForBatch()
    {
        $this->validateBatchRequestCount();

        $params = [
            'batch' => $this->convertRequestsToJson(),
            'include_headers' => true,
        ];
        $this->setParams($params);
    }

    /**
     * Validate the request count before sending them as a batch.
     *
     * @throws SDKException
     */
    public function validateBatchRequestCount()
    {
        $batchCount = count($this->requests);
        if ($batchCount === 0) {
            throw new SDKException('There are no batch requests to send.');
        }
        elseif ($batchCount > 50) {
            // Per: https://developers.facebook.com/docs/graph-api/making-multiple-requests#limits
            throw new SDKException('You cannot send more than 50 batch requests at a time.');
        }
    }

    /**
     * Converts the requests into a JSON(P) string.
     */
    public function convertRequestsToJson(): string
    {
        $requests = [];
        foreach ($this->requests as $request) {
            $options = [];

            if (null !== $request['name']) {
                $options['name'] = $request['name'];
            }

            $options = [
                ...$request['options'],

                ...$options,
            ];

            $requests[] = $this->requestEntityToBatchArray($request['request'], $options, $request['attached_files']);
        }

        return json_encode($requests);
    }

    /**
     * Converts a Request entity into an array that is batch-friendly.
     */
    public function requestEntityToBatchArray(
        Request $request,
        string|array $options = null,
        string $attachedFiles = null
    ): array
    {
        if (is_null($options)) {
            $options = [];
        }
        elseif (!is_array($options)) {
            $options = [
                'name' => $options
            ];
        }

        $compiledHeaders = [];
        $headers = $request->getHeaders();
        foreach ($headers as $name => $value) {
            $compiledHeaders[] = $name . ': ' . $value;
        }

        $batch = [
            'headers' => $compiledHeaders,
            'method' => $request->getMethod(),
            'relative_url' => $request->getUrl(),
        ];

        // Since file uploads are moved to the root request of a batch request,
        // the child requests will always be URL-encoded.
        $body = $request->getUrlEncodedBody()->getBody();
        if ($body) {
            $batch['body'] = $body;
        }

        $batch = [
            ...$options,

            ...$batch,
        ];

        if (null !== $attachedFiles) {
            $batch['attached_files'] = $attachedFiles;
        }

        return $batch;
    }

    /**
     * Get an iterator for the items.
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->requests);
    }

    /**
     * @throws SDKException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->add($value, $offset);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->requests[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        if (isset($this->requests[$offset])) {
            unset($this->requests[$offset]);
        }
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->requests[$offset] ?? null;
    }
}
