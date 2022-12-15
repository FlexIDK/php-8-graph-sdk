<?php

namespace One23\GraphSdk\Http;

use One23\GraphSdk\FileUpload\File;

/**
 * Some things copied from Guzzle
 *
 * @see https://github.com/guzzle/guzzle/blob/master/src/Post/MultipartBody.php
 */
class RequestBodyMultipart implements RequestBodyInterface
{
    /**
     * The boundary.
     */
    private string $boundary;

    public function __construct(
        private readonly array $params = [],
        private readonly  array $files = [],
        string $boundary = null
    ) {
        $this->boundary = $boundary ?: uniqid();
    }

    public function getBody(): string
    {
        $body = '';

        // Compile normal params
        $params = $this->getNestedParams($this->params);
        foreach ($params as $k => $v) {
            $body .= $this->getParamString($k, $v);
        }

        // Compile files
        foreach ($this->files as $k => $v) {
            $body .= $this->getFileString($k, $v);
        }

        // Peace out
        $body .= "--{$this->boundary}--\r\n";

        return $body;
    }

    /**
     * Returns the params as an array of nested params.
     */
    private function getNestedParams(array $params): array
    {
        $query = http_build_query($params, "", '&');
        $params = explode('&', $query);
        $result = [];

        foreach ($params as $param) {
            list($key, $value) = explode('=', $param, 2);
            $result[urldecode($key)] = urldecode($value);
        }

        return $result;
    }

    /**
     * Get the string needed to transfer a POST field.
     */
    private function getParamString(string $name, string $value): string
    {
        return sprintf(
            "--%s\r\nContent-Disposition: form-data; name=\"%s\"\r\n\r\n%s\r\n",
            $this->boundary,
            $name,
            $value
        );
    }

    /**
     * Get the string needed to transfer a file.
     */
    private function getFileString(string $name, File $file): string
    {
        return sprintf(
            "--%s\r\nContent-Disposition: form-data; name=\"%s\"; filename=\"%s\"%s\r\n\r\n%s\r\n",
            $this->boundary,
            $name,
            $file->getFileName(),
            $this->getFileHeaders($file),
            $file->getContents()
        );
    }

    /**
     * Get the headers needed before transferring the content of a POST file.
     */
    protected function getFileHeaders(File $file): string
    {
        return "\r\nContent-Type: {$file->getMimetype()}";
    }

    /**
     * Get the boundary
     */
    public function getBoundary(): string
    {
        return $this->boundary;
    }
}
