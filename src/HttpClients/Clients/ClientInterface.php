<?php

namespace One23\GraphSdk\HttpClients\Clients;

use One23\GraphSdk\Exceptions\SDKException;
use One23\GraphSdk\Http\GraphRawResponse;

interface ClientInterface
{
    /**
     * Sends a request to the server and returns the raw response.
     *
     * @throws SDKException
     */
    public function send(string $url, string $method, string $body, array $headers, int $timeOut): GraphRawResponse;
}
