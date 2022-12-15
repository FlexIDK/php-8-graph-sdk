<?php

namespace One23\GraphSdk\PersistentData\Handlers;

use One23\GraphSdk\Exceptions\SDKException;

class SessionPhalcon implements PersistentDataInterface
{
    /**
     * Prefix to use for session variables.
     */
    protected string $sessionPrefix = 'FBRLH_';

    protected \Phalcon\Session\Manager $session;

    /**
     * @throws SDKException
     */
    public function __construct(bool $enableSessionCheck = true)
    {
        $di = \Phalcon\Di\Di::getDefault();
        $session = $di->get('session');

        if (!$session instanceof \Phalcon\Session\Manager) {
            throw new SDKException(
                "Phalcon Di service 'session' are not exist.",
                720
            );
        }

        $this->session = $session;

        if ($enableSessionCheck && $this->session->start()) {
            throw new SDKException(
                'Phalcon sessions are not active.',
                720
            );
        }
    }

    public function get(string $key): mixed
    {
        return $this->session->get($this->sessionPrefix . $key);
    }

    public function set(string $key, mixed $value): static
    {
        $this->session->set($this->sessionPrefix . $key, $value);

        return $this;
    }
}
