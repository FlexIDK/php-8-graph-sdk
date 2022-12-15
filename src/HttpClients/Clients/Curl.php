<?php

namespace One23\GraphSdk\HttpClients\Clients;

use One23\GraphSdk\Http\GraphRawResponse;
use One23\GraphSdk\Exceptions\SDKException;
use One23\GraphSdk\HttpClients\Curl as HCCurl;

class Curl implements ClientInterface
{
    /**
     * The client error message
     */
    protected string $curlErrorMessage = '';

    /**
     * The curl client error code
     */
    protected int $curlErrorCode = 0;

    /**
     * The raw response from the server
     */
    protected string|bool $rawResponse;

    /**
     * @var HCCurl Procedural curl as object
     */
    protected HCCurl $facebookCurl;

    public function __construct(HCCurl $facebookCurl = null)
    {
        $this->facebookCurl = $facebookCurl ?: new HCCurl();
    }

    public function send(string $url, string $method, string $body, array $headers, int $timeOut): GraphRawResponse
    {
        $this->openConnection($url, $method, $body, $headers, $timeOut);
        $this->sendRequest();

        if ($curlErrorCode = $this->facebookCurl->errno()) {
            throw new SDKException($this->facebookCurl->error(), $curlErrorCode);
        }

        // Separate the raw headers from the raw body
        list($rawHeaders, $rawBody) = $this->extractResponseHeadersAndBody();

        $this->closeConnection();

        return new GraphRawResponse($rawHeaders, $rawBody);
    }

    /**
     * Opens a new curl connection.
     */
    public function openConnection(string $url, string $method, string $body, array $headers, int $timeOut)
    {
        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->compileRequestHeaders($headers),
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => $timeOut,
            CURLOPT_RETURNTRANSFER => true, // Return response as string
            CURLOPT_HEADER => true, // Enable header processing
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO => __DIR__ . '/certs/DigiCertHighAssuranceEVRootCA.pem',
        ];

        if ($method !== "GET") {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        $this->facebookCurl->init();
        $this->facebookCurl->setoptArray($options);
    }

    /**
     * Compiles the request headers into a curl-friendly format.
     */
    public function compileRequestHeaders(array $headers): array
    {
        $return = [];

        foreach ($headers as $key => $value) {
            $return[] = $key . ': ' . $value;
        }

        return $return;
    }

    /**
     * Send the request and get the raw response from curl
     */
    public function sendRequest(): void
    {
        $this->rawResponse = $this->facebookCurl->exec();
    }

    /**
     * Extracts the headers and the body into a two-part array
     */
    public function extractResponseHeadersAndBody(): array
    {
        $parts = explode("\r\n\r\n", $this->rawResponse);
        $rawBody = array_pop($parts);
        $rawHeaders = implode("\r\n\r\n", $parts);

        return [trim($rawHeaders), trim($rawBody)];
    }

    /**
     * Closes an existing curl connection
     */
    public function closeConnection(): void
    {
        $this->facebookCurl->close();
    }
}
