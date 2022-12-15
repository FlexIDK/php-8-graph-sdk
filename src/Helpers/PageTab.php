<?php

namespace One23\GraphSdk\Helpers;

use One23\GraphSdk\App;
use One23\GraphSdk\Client;

class PageTab extends Canvas
{
    protected ?array $pageData = null;

    public function __construct(
        App $app,
        Client $client,
        string $graphVersion = null
    ) {
        parent::__construct($app, $client, $graphVersion);

        if (!$this->signedRequest) {
            return;
        }

        $this->pageData = self::mapType(
            $this->signedRequest->get('page'),
            'arrOrBlank'
        );
    }

    /**
     * Returns true if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return self::mapType(
            $this->getPageData('admin'),
            'bool'
        );
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
