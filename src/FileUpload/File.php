<?php

namespace One23\GraphSdk\FileUpload;

use One23\GraphSdk\Exceptions\SDKException;

class File
{
    /**
     * @var resource The stream pointing to the file.
     */
    protected $stream;

    /**
     * @throws SDKException
     */
    public function __construct(
        protected string $path,
        private int $maxLength = -1,
        private int $offset = -1
    ) {
        $this->open();
    }

    /**
     * Opens a stream for the file.
     *
     * @throws SDKException
     */
    public function open(): void
    {
        if (!$this->isRemoteFile($this->path) && !is_readable($this->path)) {
            throw new SDKException('Failed to create FacebookFile entity. Unable to read resource: ' . $this->path . '.');
        }

        $this->stream = fopen($this->path, 'r');

        if (!$this->stream) {
            throw new SDKException('Failed to create FacebookFile entity. Unable to open resource: ' . $this->path . '.');
        }
    }

    /**
     * Returns true if the path to the file is remote.
     */
    protected function isRemoteFile(string $pathToFile): bool
    {
        return preg_match('/^(https?|ftp):\/\/.*/', $pathToFile) === 1;
    }

    /**
     * Closes the stream when destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Stops the file stream.
     */
    public function close(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * Return the contents of the file.
     */
    public function getContents(): string
    {
        return stream_get_contents($this->stream, $this->maxLength, $this->offset);
    }

    /**
     * Return the name of the file.
     */
    public function getFileName(): string
    {
        return basename($this->path);
    }

    /**
     * Return the path of the file.
     */
    public function getFilePath(): string
    {
        return $this->path;
    }

    /**
     * Return the size of the file.
     */
    public function getSize(): int
    {
        return filesize($this->path);
    }

    /**
     * Return the mimetype of the file.
     */
    public function getMimetype(): string
    {
        return Mimetypes::getInstance()->fromFilename($this->path)
            ?: 'text/plain';
    }
}
