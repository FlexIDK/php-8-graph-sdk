<?php

namespace One23\GraphSdk\Helpers;

/**
 * Class FacebookJavaScriptLoginHelper

 */
class FacebookJavaScriptHelper extends FacebookSignedRequestFromInputHelper
{
    /**
     * Get raw signed request from the cookie.
     */
    public function getRawSignedRequest(): ?string
    {
        return $this->getRawSignedRequestFromCookie();
    }
}
