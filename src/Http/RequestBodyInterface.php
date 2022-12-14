<?php

namespace One23\GraphSdk\Http;

interface RequestBodyInterface
{
    /**
     * Get the body of the request to send to Graph.
     */
    public function getBody(): string;
}
