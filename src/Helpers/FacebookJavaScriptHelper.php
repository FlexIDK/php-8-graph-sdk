<?php

namespace One23\GraphSdk\Helpers;

class FacebookJavaScriptHelper extends AbstractSignedRequestFromInput
{
    /**
     * Get raw signed request from the cookie.
     */
    public function getRawSignedRequest(): ?string
    {
        return $this->getRawSignedRequestFromCookie();
    }
}
