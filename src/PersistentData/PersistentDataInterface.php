<?php

namespace One23\GraphSdk\PersistentData;

/**
 * Interface PersistentDataInterface

 */
interface PersistentDataInterface
{
    /**
     * Get a value from a persistent data store.
     */
    public function get(string $key): mixed;

    /**
     * Set a value in the persistent data store.
     */
    public function set(string $key, mixed $value): static;
}
