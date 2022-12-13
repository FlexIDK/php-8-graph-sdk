<?php

namespace One23\GraphSdk\Helpers;

/**
 * Class FacebookCanvasLoginHelper

 */
class FacebookCanvasHelper extends FacebookSignedRequestFromInputHelper
{
    /**
     * Returns the app data value.
     */
    public function getAppData(): mixed
    {
        return $this->signedRequest?->get('app_data');
    }

    /**
     * Get raw signed request from POST.
     *
     * @return string|null
     */
    public function getRawSignedRequest(): ?string
    {
        return $this->getRawSignedRequestFromPost() ?: null;
    }
}
