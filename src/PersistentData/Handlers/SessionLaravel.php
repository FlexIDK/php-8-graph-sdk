<?php

namespace One23\GraphSdk\PersistentData\Handlers;

use Illuminate\Support\Facades\Session;
use One23\GraphSdk\Exceptions\SDKException;

class SessionLaravel implements PersistentDataInterface
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
        if ($enableSessionCheck && Session::start()) {
            throw new SDKException(
                'Laravel sessions are not active.',
                720
            );
        }
    }

    public function get(string $key): mixed
    {
        return Session::get($this->sessionPrefix . $key);
    }

    public function set(string $key, mixed $value): static
    {
        Session::put($this->sessionPrefix . $key, $value);

        return $this;
    }
}
