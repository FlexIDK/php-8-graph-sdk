<?php

namespace One23\GraphSdk\Helpers;

class JavaScript extends AbstractSignedRequestFromInput
{
    /**
     * Get raw signed request from the cookie.
     */
    public function getRawSignedRequest(): ?string
    {
        return self::mapType(
            $this->getRawSignedRequestFromCookie(),
            'str'
        );
    }
}
