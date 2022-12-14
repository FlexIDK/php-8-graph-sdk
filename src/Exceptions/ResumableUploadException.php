<?php

namespace One23\GraphSdk\Exceptions;

class ResumableUploadException extends SDKException
{
    protected ?int $startOffset = null;

    protected ?int $endOffset = null;

    public function getStartOffset(): ?int
    {
        return $this->startOffset;
    }

    public function setStartOffset(?int $startOffset): self
    {
        $this->startOffset = $startOffset;

        return $this;
    }

    public function getEndOffset(): ?int
    {
        return $this->endOffset;
    }

    public function setEndOffset(?int $endOffset): self
    {
        $this->endOffset = $endOffset;

        return $this;
    }
}
