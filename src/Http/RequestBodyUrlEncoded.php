<?php

namespace One23\GraphSdk\Http;

class RequestBodyUrlEncoded implements RequestBodyInterface
{
    public function __construct(protected array $params)
    {
    }

    public function getBody(): string
    {
        return http_build_query($this->params, null, '&');
    }
}
