<?php

namespace One23\GraphSdk\HttpClients\Clients;

use One23\GraphSdk\Http\GraphRawResponse;
use One23\GraphSdk\Exceptions\SDKException;
use One23\GraphSdk\HttpClients\Stream as HCStream;

class Stream implements ClientInterface
{
    protected HCStream $stream;

    public function __construct(HCStream $facebookStream = null)
    {
        $this->stream = $facebookStream ?: new HCStream();
    }

    public function send($url, $method, $body, array $headers, $timeOut): GraphRawResponse
    {
        $options = [
            'http' => [
                'method' => $method,
                'header' => $this->compileHeader($headers),
                'content' => $body,
                'timeout' => $timeOut,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => true, // All root certificates are self-signed
                'cafile' => __DIR__ . '/../certs/DigiCertHighAssuranceEVRootCA.pem',
            ],
        ];

        $this->stream->streamContextCreate($options);
        $rawBody = $this->stream->fileGetContents($url);
        $rawHeaders = $this->stream->getResponseHeaders();

        if ($rawBody === false || empty($rawHeaders)) {
            throw new SDKException('Stream returned an empty response', 660);
        }

        $rawHeaders = implode("\r\n", $rawHeaders);

        return new GraphRawResponse($rawHeaders, $rawBody);
    }

    /**
     * Formats the headers for use in the stream wrapper.
     */
    public function compileHeader(array $headers): string
    {
        $header = [];
        foreach ($headers as $k => $v) {
            $header[] = $k . ': ' . $v;
        }

        return implode("\r\n", $header);
    }
}
