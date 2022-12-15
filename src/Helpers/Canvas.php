<?php

namespace One23\GraphSdk\Helpers;

class Canvas extends AbstractSignedRequestFromInput
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
     */
    public function getRawSignedRequest(): ?string
    {
        return $this->getRawSignedRequestFromPost() ?: null;
    }
}
