<?php

namespace One23\GraphSdk\HttpClients;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Exception;

class HttpClientsFactory
{
    private function __construct()
    {
    }

    /**
     * HTTP client generation.
     *
     * @throws Exception|InvalidArgumentException
     */
    public static function createHttpClient(Clients\Guzzle|Client|string $handler = null): Clients\ClientInterface
    {
        if (!$handler) {
            return self::detectDefaultClient();
        }

        if ($handler instanceof Clients\ClientInterface) {
            return $handler;
        }

        if ('stream' === $handler) {
            return new Clients\Stream();
        }
        if ('curl' === $handler) {
            if (!extension_loaded('curl')) {
                throw new Exception('The cURL extension must be loaded in order to use the "curl" handler.');
            }

            return new Clients\Curl();
        }

        if ('guzzle' === $handler && !class_exists('GuzzleHttp\Client')) {
            throw new Exception('The Guzzle HTTP client must be included in order to use the "guzzle" handler.');
        }

        if ($handler instanceof Client) {
            return new Clients\Guzzle($handler);
        }
        if ('guzzle' === $handler) {
            return new Clients\Guzzle();
        }

        throw new InvalidArgumentException(
            'The http client handler must be set to "curl", "stream", "guzzle",' .
            ' be an instance of ' . Client::class .
            ' or an instance of ' . Clients\ClientInterface::class);
    }

    /**
     * Detect default HTTP client.
     */
    private static function detectDefaultClient(): Clients\ClientInterface
    {
        if (extension_loaded('curl')) {
            return new Clients\Curl();
        }

        if (class_exists('GuzzleHttp\Client')) {
            return new Clients\Guzzle();
        }

        return new Clients\Stream();
    }
}
