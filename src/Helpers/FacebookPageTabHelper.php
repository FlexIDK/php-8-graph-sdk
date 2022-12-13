<?php

namespace One23\GraphSdk\Helpers;

use One23\GraphSdk\FacebookApp;
use One23\GraphSdk\FacebookClient;

/**
 * Class FacebookPageTabHelper

 */
class FacebookPageTabHelper extends FacebookCanvasHelper
{
    /**
     * @var array|null
     */
    protected $pageData;

    /**
     * Initialize the helper and process available signed request data.
     *
     * @param FacebookApp    $app          The FacebookApp entity.
     * @param FacebookClient $client       The client to make HTTP requests.
     * @param string|null    $graphVersion The version of Graph to use.
     */
    public function __construct(FacebookApp $app, FacebookClient $client, $graphVersion = null)
    {
        parent::__construct($app, $client, $graphVersion);

        if (!$this->signedRequest) {
            return;
        }

        $this->pageData = $this->signedRequest->get('page');
    }

    /**
     * Returns true if the user is an admin.
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->getPageData('admin') === true;
    }

    /**
     * Returns a value from the page data.
     *
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function getPageData($key, $default = null)
    {
        if (isset($this->pageData[$key])) {
            return $this->pageData[$key];
        }

        return $default;
    }

    /**
     * Returns the page id if available.
     *
     * @return string|null
     */
    public function getPageId()
    {
        return $this->getPageData('id');
    }
}