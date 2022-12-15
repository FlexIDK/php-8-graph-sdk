<?php

namespace One23\GraphSdk\HttpClients;

/**
 * Abstraction for the procedural curl elements so that curl can be mocked and the implementation can be tested.
 */
class Curl
{
    /**
     * Curl resource instance
     */
    protected \CurlHandle $curl;

    /**
     * Make a new curl reference instance
     */
    public function init()
    {
        $this->curl = curl_init();
    }

    /**
     * Set a curl option
     */
    public function setopt(string $key, mixed $value): static
    {
        curl_setopt($this->curl, $key, $value);

        return $this;
    }

    /**
     * Set an array of options to a curl resource
     */
    public function setoptArray(array $options): static
    {
        curl_setopt_array($this->curl, $options);

        return $this;
    }

    /**
     * Send a curl request
     */
    public function exec(): mixed
    {
        return curl_exec($this->curl);
    }

    /**
     * Return the curl error number
     */
    public function errno(): int
    {
        return curl_errno($this->curl);
    }

    /**
     * Return the curl error message
     */
    public function error(): string
    {
        return curl_error($this->curl);
    }

    /**
     * Get info from a curl reference
     */
    public function getinfo(int $type): mixed
    {
        return curl_getinfo($this->curl, $type);
    }

    /**
     * Get the currently installed curl version
     */
    public function version(): array
    {
        return curl_version();
    }

    /**
     * Close the resource connection to curl
     */
    public function close(): void
    {
        curl_close($this->curl);
    }
}
