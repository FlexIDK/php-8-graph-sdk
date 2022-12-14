<?php

namespace One23\GraphSdk\FileUpload;

class TransferChunk
{
    public function __construct(
        private File $file,
        private int $uploadSessionId,
        private int $videoId,
        private int $startOffset,
        private int $endOffset
    ) {
    }

    /**
     * Return the file entity.
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * Return a FacebookFile entity with partial content.
     */
    public function getPartialFile(): File
    {
        $maxLength = $this->endOffset - $this->startOffset;

        return new File($this->file->getFilePath(), $maxLength, $this->startOffset);
    }

    /**
     * Return upload session Id
     */
    public function getUploadSessionId(): int
    {
        return $this->uploadSessionId;
    }

    /**
     * Check whether is the last chunk
     */
    public function isLastChunk(): bool
    {
        return !!($this->startOffset === $this->endOffset);
    }

    public function getStartOffset(): int
    {
        return $this->startOffset;
    }

    public function getEndOffset(): int
    {
        return $this->endOffset;
    }

    /**
     * Get uploaded video Id
     */
    public function getVideoId(): int
    {
        return $this->videoId;
    }
}
