<?php

namespace One23\GraphSdk\Url;

interface DetectionInterface
{
    /**
     * Get the currently active URL.
     */
    public function getCurrentUrl(): string;
}
