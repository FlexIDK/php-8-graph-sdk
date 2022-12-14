<?php

namespace One23\GraphSdk\PersistentData;

use One23\GraphSdk\Exceptions\SDKException;

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
     * @throws SDKException
     */
    public function __construct(bool $enableSessionCheck = true)
    {
        if ($enableSessionCheck && session_status() !== PHP_SESSION_ACTIVE) {
            throw new SDKException(
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
