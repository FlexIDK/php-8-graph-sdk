<?php

namespace One23\GraphSdk\Exceptions;

/**
 * Class FacebookResumableUploadException

 */
class FacebookResumableUploadException extends FacebookSDKException
{
    protected $startOffset;

    protected $endOffset;

    /**
     * @return int|null
     */
    public function getStartOffset()
    {
        return $this->startOffset;
    }

    /**
     * @param int|null $startOffset
     */
    public function setStartOffset($startOffset)
    {
        $this->startOffset = $startOffset;
    }

    /**
     * @return int|null
     */
    public function getEndOffset()
    {
        return $this->endOffset;
    }

    /**
     * @param int|null $endOffset
     */
    public function setEndOffset($endOffset)
    {
        $this->endOffset = $endOffset;
    }
}
