<?php

namespace One23\GraphSdk\HttpClients\Clients;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use One23\GraphSdk\Http\GraphRawResponse;
use One23\GraphSdk\Exceptions\SDKException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class Guzzle implements ClientInterface
{
    protected Client $guzzleClient;

    public function __construct(Client $guzzleClient = null)
    {
        $this->guzzleClient = $guzzleClient ?: new Client();
    }

    /**
     * @inheritdoc
     */
    public function send(string $url, string $method, string $body, array $headers, int $timeOut): GraphRawResponse
    {
        $options = [
            'timeout' => $timeOut,
            'connect_timeout' => 10,
            'verify' => __DIR__ . '/../certs/DigiCertHighAssuranceEVRootCA.pem',
//            'headers' => $headers,
//            'body' => $body,
        ];
        $request = new Request($method, $url, $headers, $body);

        try {
            $rawResponse = $this->guzzleClient->send(
                $request,
                $options
            );
        }
        catch (ClientException $e) {
            $rawResponse = $e->getResponse();
        }
        catch (GuzzleException $e) {
            throw new SDKException($e->getMessage(), $e->getCode());
        }

        $rawHeaders = $this->getHeadersAsString($rawResponse);
        $rawBody = $rawResponse->getBody();
        $httpStatusCode = $rawResponse->getStatusCode();

        return new GraphRawResponse($rawHeaders, $rawBody, $httpStatusCode);
    }

    /**
     * Returns the Guzzle array of headers as a string.
     */
    public function getHeadersAsString(ResponseInterface $response): string
    {
        $headers = $response->getHeaders();
        $rawHeaders = [];
        foreach ($headers as $name => $values) {
            $rawHeaders[] = $name . ": " . implode(", ", $values);
        }

        return implode("\r\n", $rawHeaders);
    }
}
