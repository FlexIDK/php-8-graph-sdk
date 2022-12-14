<?php

namespace One23\GraphSdk\FileUpload;

use GuzzleHttp\Psr7\MimeType;

class Mimetypes
{
    protected static Mimetypes $instance;

    /**
     * Get a singleton instance of the class
     */
    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get a mimetype from a filename
     */
    public function fromFilename(string $filename): ?string
    {
        return MimeType::fromFilename($filename);
    }

    /**
     * Get a mimetype value from a file extension
     */
    public function fromExtension(string $extension): ?string
    {
        return MimeType::fromExtension($extension);
    }
}
