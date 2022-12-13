<?php

namespace One23\GraphSdk\PersistentData;

/**
 * Class FacebookMemoryPersistentDataHandler
 */
class FacebookMemoryPersistentDataHandler implements PersistentDataInterface
{
    /**
     * The session data to keep in memory.
     */
    protected array $sessionData = [];

    public function get($key)
    {
        return $this->sessionData[$key] ?? null;
    }

    public function set($key, $value)
    {
        $this->sessionData[$key] = $value;
    }
}
