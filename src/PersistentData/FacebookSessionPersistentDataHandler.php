<?php

namespace One23\GraphSdk\PersistentData;

use One23\GraphSdk\Exceptions\FacebookSDKException;

/**
 * Class FacebookSessionPersistentDataHandler

 */
class FacebookSessionPersistentDataHandler implements PersistentDataInterface
{
    /**
     * Prefix to use for session variables.
     */
    protected string $sessionPrefix = 'FBRLH_';

    /**
     * Init the session handler.
     *
     * @throws FacebookSDKException
     */
    public function __construct(bool $enableSessionCheck = true)
    {
        if ($enableSessionCheck && session_status() !== PHP_SESSION_ACTIVE) {
            throw new FacebookSDKException(
                'Sessions are not active. Please make sure session_start() is at the top of your script.',
                720
            );
        }
    }

    public function get($key)
    {
        return isset($_SESSION[$this->sessionPrefix . $key]) ?? null;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $_SESSION[$this->sessionPrefix . $key] = $value;
    }
}
