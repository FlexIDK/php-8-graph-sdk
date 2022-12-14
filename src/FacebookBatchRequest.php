<?php

namespace One23\GraphSdk;

use ArrayIterator;
use IteratorAggregate;
use ArrayAccess;
use One23\GraphSdk\Authentication\AccessToken;
use One23\GraphSdk\Exceptions\SDKException;

/**
 * Class BatchRequest

 */
class FacebookBatchRequest extends FacebookRequest implements IteratorAggregate, ArrayAccess
{
    /**
     * @var array An array of FacebookRequest entities to send.
     */
    protected $requests = [];

    /**
     * @var array An array of files to upload.
     */
    protected $attachedFiles;

    /**
     * Creates a new Request entity.
     *
     * @param FacebookApp|null        $app
     * @param array                   $requests
     * @param AccessToken|string|null $accessToken
     * @param string|null             $graphVersion
     */
    public function __construct(FacebookApp $app = null, array $requests = [], $accessToken = null, $graphVersion = null)
    {
        parent::__construct($app, $accessToken, 'POST', '', [], null, $graphVersion);

        $this->add($requests);
    }

    /**
     * Adds a new request to the array.
     *
     * @param FacebookRequest|array $request
     * @param string|null|array     $options Array of batch request options e.g. 'name', 'omit_response_on_success'.
     *                                       If a string is given, it is the value of the 'name' option.
     *
     * @return FacebookBatchRequest
     *
     * @throws \InvalidArgumentException
     */
    public function add($request, $options = null)
    {
        if (is_array($request)) {
            foreach ($request as $key => $req) {
                $this->add($req, $key);
            }

            return $this;
        }

        if (!$request instanceof FacebookRequest) {
            throw new \InvalidArgumentException('Argument for add() must be of type array or FacebookRequest.');
        }

        if (null === $options) {
            $options = [];
        } elseif (!is_array($options)) {
            $options = ['name' => $options];
        }

        $this->addFallbackDefaults($request);

        // File uploads
        $attachedFiles = $this->extractFileAttachments($request);

        $name = isset($options['name']) ? $options['name'] : null;

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
     * Ensures that the FacebookApp and access token fall back when missing.
     *
     * @param FacebookRequest $request
     *
     * @throws SDKException
     */
    public function addFallbackDefaults(FacebookRequest $request)
    {
        if (!$request->getApp()) {
            $app = $this->getApp();
            if (!$app) {
                throw new SDKException('Missing FacebookApp on FacebookRequest and no fallback detected on FacebookBatchRequest.');
            }
            $request->setApp($app);
        }

        if (!$request->getAccessToken()) {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                throw new SDKException('Missing access token on FacebookRequest and no fallback detected on FacebookBatchRequest.');
            }
            $request->setAccessToken($accessToken);
        }
    }

    /**
     * Extracts the files from a request.
     *
     * @param FacebookRequest $request
     *
     * @return string|null
     *
     * @throws SDKException
     */
    public function extractFileAttachments(FacebookRequest $request)
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
     * Return the FacebookRequest entities.
     *
     * @return array
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Prepares the requests to be sent as a batch request.
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
        } elseif ($batchCount > 50) {
            // Per: https://developers.facebook.com/docs/graph-api/making-multiple-requests#limits
            throw new SDKException('You cannot send more than 50 batch requests at a time.');
        }
    }

    /**
     * Converts the requests into a JSON(P) string.
     *
     * @return string
     */
    public function convertRequestsToJson()
    {
        $requests = [];
        foreach ($this->requests as $request) {
            $options = [];

            if (null !== $request['name']) {
                $options['name'] = $request['name'];
            }

            $options += $request['options'];

            $requests[] = $this->requestEntityToBatchArray($request['request'], $options, $request['attached_files']);
        }

        return json_encode($requests);
    }

    /**
     * Converts a Request entity into an array that is batch-friendly.
     *
     * @param FacebookRequest   $request       The request entity to convert.
     * @param string|null|array $options       Array of batch request options e.g. 'name', 'omit_response_on_success'.
     *                                         If a string is given, it is the value of the 'name' option.
     * @param string|null       $attachedFiles Names of files associated with the request.
     *
     * @return array
     */
    public function requestEntityToBatchArray(FacebookRequest $request, $options = null, $attachedFiles = null)
    {

        if (null === $options) {
            $options = [];
        } elseif (!is_array($options)) {
            $options = ['name' => $options];
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

        $batch += $options;

        if (null !== $attachedFiles) {
            $batch['attached_files'] = $attachedFiles;
        }

        return $batch;
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->requests);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->add($value, $offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->requests[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->requests[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return isset($this->requests[$offset]) ? $this->requests[$offset] : null;
    }
}
