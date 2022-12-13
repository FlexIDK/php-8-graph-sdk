<?php

namespace One23\GraphSdk\Http;

/**
 * Interface

 */
interface RequestBodyInterface
{
    /**
     * Get the body of the request to send to Graph.
     *
     * @return string
     */
    public function getBody();
}
