<?php

namespace One23\GraphSdk\PersistentData\Handlers;

use One23\GraphSdk\Exceptions\SDKException;

class Session implements PersistentDataInterface
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

    public function get(string $key): mixed
    {
        return isset($_SESSION[$this->sessionPrefix . $key]) ?? null;
    }

    public function set(string $key, mixed $value): static
    {
        $_SESSION[$this->sessionPrefix . $key] = $value;

        return $this;
    }
}
