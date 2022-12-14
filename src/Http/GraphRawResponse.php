<?php

namespace One23\GraphSdk\Http;

class GraphRawResponse
{
    /**
     * The response headers in the form of an associative array.
     */
    protected array $headers;

    protected int $httpResponseCode;

    public function __construct(
        string|array $headers,
        protected string $body,
        ?int $httpStatusCode = null
    ) {
        if (is_array($headers)) {
            $this->headers = $headers;
        }
        else {
            $this->setHeadersFromString($headers);
        }

        if ($httpStatusCode) {
            $this->setHttpResponseCode($httpStatusCode);
        }
    }

    protected function setHttpResponseCode(int $code) {
        if ($code < 0) {
            throw new \InvalidArgumentException("'httpStatusCode' expects a value greater than 0");
        }

        $this->httpResponseCode = $code;
    }

    /**
     * Parse the raw headers and set as an array.
     */
    protected function setHeadersFromString(string $rawHeaders): void
    {
        // Normalize line breaks
        $rawHeaders = str_replace("\r\n", "\n", $rawHeaders);

        // There will be multiple headers if a 301 was followed
        // or a proxy was followed, etc
        $headerCollection = explode("\n\n", trim($rawHeaders));
        // We just want the last response (at the end)
        $rawHeader = array_pop($headerCollection);

        $headerComponents = explode("\n", $rawHeader);
        foreach ($headerComponents as $line) {
            if (!str_contains($line, ': ')) {
                $this->setHttpResponseCodeFromHeader($line);
            }
            else {
                list($key, $value) = explode(': ', $line, 2);
                $this->headers[$key] = $value;
            }
        }
    }

    /**
     * Sets the HTTP response code from a raw header.
     */
    public function setHttpResponseCodeFromHeader(string $rawResponseHeader): void
    {
        // https://tools.ietf.org/html/rfc7230#section-3.1.2
        list($version, $status, $reason) = array_pad(explode(' ', $rawResponseHeader, 3), 3, null);

        $this->setHttpResponseCode((int)$status);
    }

    /**
     * Return the response headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Return the body of the response.
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Return the HTTP response code.
     */
    public function getHttpResponseCode(): int
    {
        return $this->httpResponseCode ?? 0;
    }
}
