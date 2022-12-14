<?php

namespace One23\GraphSdk\FileUpload;

use One23\GraphSdk\Exceptions\ResponseException;
use One23\GraphSdk\Exceptions\ResumableUploadException;
use One23\GraphSdk\Exceptions\SDKException;
use One23\GraphSdk\FacebookApp;
use One23\GraphSdk\FacebookClient;
use One23\GraphSdk\FacebookRequest;

class ResumableUploader
{
    public function __construct(
        protected FacebookApp $app,
        protected FacebookClient $client,
        protected $accessToken,
        protected $graphVersion
    ) {
    }

    /**
     * Upload by chunks - start phase
     *
     * @throws SDKException
     */
    public function start(string $endpoint, File $file): TransferChunk
    {
        $params = [
            'upload_phase' => 'start',
            'file_size' => $file->getSize(),
        ];
        $response = $this->sendUploadRequest($endpoint, $params);

        return new TransferChunk($file, $response['upload_session_id'], $response['video_id'], $response['start_offset'], $response['end_offset']);
    }

    /**
     * Helper to make a FacebookRequest and send it.
     *
     * @throws SDKException
     */
    private function sendUploadRequest(string $endpoint, array $params = []): array
    {
        $request = new FacebookRequest($this->app, $this->accessToken, 'POST', $endpoint, $params, null, $this->graphVersion);

        return $this->client->sendRequest($request)->getDecodedBody();
    }

    /**
     * Upload by chunks - transfer phase
     *
     * @throws ResponseException|SDKException
     */
    public function transfer(string $endpoint, TransferChunk $chunk, bool $allowToThrow = false): TransferChunk
    {
        $params = [
            'upload_phase' => 'transfer',
            'upload_session_id' => $chunk->getUploadSessionId(),
            'start_offset' => $chunk->getStartOffset(),
            'video_file_chunk' => $chunk->getPartialFile(),
        ];

        try {
            $response = $this->sendUploadRequest($endpoint, $params);
        }
        catch (ResponseException $e) {
            $preException = $e->getPrevious();
            if ($allowToThrow || !$preException instanceof ResumableUploadException) {
                throw $e;
            }

            if (null !== $preException->getStartOffset() && null !== $preException->getEndOffset()) {
                return new TransferChunk(
                    $chunk->getFile(),
                    $chunk->getUploadSessionId(),
                    $chunk->getVideoId(),
                    $preException->getStartOffset(),
                    $preException->getEndOffset()
                );
            }

            // Return the same chunk entity so it can be retried.
            return $chunk;
        }

        return new TransferChunk($chunk->getFile(), $chunk->getUploadSessionId(), $chunk->getVideoId(), $response['start_offset'], $response['end_offset']);
    }

    /**
     * Upload by chunks - finish phase
     *
     * @throws SDKException
     */
    public function finish(string $endpoint, string $uploadSessionId, array $metadata = []): bool
    {
        $params = array_merge($metadata, [
            'upload_phase' => 'finish',
            'upload_session_id' => $uploadSessionId,
        ]);
        $response = $this->sendUploadRequest($endpoint, $params);

        return $response['success'];
    }
}
