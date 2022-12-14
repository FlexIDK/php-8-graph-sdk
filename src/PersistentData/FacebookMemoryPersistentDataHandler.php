<?php

namespace One23\GraphSdk\PersistentData;

class FacebookMemoryPersistentDataHandler implements PersistentDataInterface
{
    /**
     * The session data to keep in memory.
     */
    protected array $sessionData = [];

    public function get(string $key): mixed
    {
        return $this->sessionData[$key] ?? null;
    }

    public function set(string $key, mixed $value): static
    {
        $this->sessionData[$key] = $value;

        return $this;
    }
}
