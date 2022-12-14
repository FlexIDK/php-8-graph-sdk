<?php

namespace One23\GraphSdk\Helpers;

use One23\GraphSdk\FacebookApp;
use One23\GraphSdk\FacebookClient;

class FacebookPageTabHelper extends FacebookCanvasHelper
{
    protected ?array $pageData = null;

    public function __construct(
        FacebookApp $app,
        FacebookClient $client,
        string $graphVersion = null
    ) {
        parent::__construct($app, $client, $graphVersion);

        if (!$this->signedRequest) {
            return;
        }

        $this->pageData = $this->signedRequest->get('page');
    }

    /**
     * Returns true if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return !!($this->getPageData('admin') === true);
    }

    /**
     * Returns a value from the page data.
     */
    public function getPageData(string $key, mixed $default = null): mixed
    {
        if (isset($this->pageData[$key])) {
            return $this->pageData[$key];
        }

        return $default;
    }

    /**
     * Returns the page id if available.
     */
    public function getPageId(): ?string
    {
        return self::mapType(
            $this->getPageData('id'),
            'str'
        );
    }
}
