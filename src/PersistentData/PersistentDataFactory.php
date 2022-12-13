<?php

namespace One23\GraphSdk\PersistentData;

use InvalidArgumentException;

class PersistentDataFactory
{
    private function __construct()
    {
        // a factory constructor should never be invoked
    }

    /**
     * PersistentData generation.
     *
     * @param PersistentDataInterface|string|null $handler
     *
     * @throws InvalidArgumentException If the persistent data handler isn't "session", "memory", or an instance of One23\GraphSdk\PersistentData\PersistentDataInterface.
     *
     * @return PersistentDataInterface
     */
    public static function createPersistentDataHandler($handler)
    {
        if (!$handler) {
            return session_status() === PHP_SESSION_ACTIVE
                ? new FacebookSessionPersistentDataHandler()
                : new FacebookMemoryPersistentDataHandler();
        }

        if ($handler instanceof PersistentDataInterface) {
            return $handler;
        }

        if ('session' === $handler) {
            return new FacebookSessionPersistentDataHandler();
        }
        if ('memory' === $handler) {
            return new FacebookMemoryPersistentDataHandler();
        }

        throw new InvalidArgumentException('The persistent data handler must be set to "session", "memory", or be an instance of One23\GraphSdk\PersistentData\PersistentDataInterface');
    }
}
