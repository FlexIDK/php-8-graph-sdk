<?php

namespace One23\GraphSdk\PersistentData;

use InvalidArgumentException;
use One23\GraphSdk\PersistentData\Handlers\PersistentDataInterface;

class PersistentDataFactory
{
    private function __construct()
    {
    }

    /**
     * PersistentData generation.
     *
     * @throws InvalidArgumentException
     */
    public static function createPersistentDataHandler(PersistentDataInterface|string $handler = null): PersistentDataInterface
    {
        $handlers = [
            'session', 'memory', 'laravel', 'phalcon',
        ];

        if (!$handler) {
            if (class_exists('Illuminate\Support\Facades\Session')) {
                return self::createPersistentDataHandler('laravel');
            }
            elseif (class_exists('Phalcon\Session\Manager')) {
                return self::createPersistentDataHandler('phalcon');
            }
            elseif (session_status() === PHP_SESSION_ACTIVE) {
                return self::createPersistentDataHandler('session');
            }
            else {
                return self::createPersistentDataHandler('memory');
            }
        }

        if ($handler instanceof PersistentDataInterface) {
            return $handler;
        }

        return match ($handler) {
            'session' => new Handlers\Session(),
            'memory' => new Handlers\Memory(),
            'laravel' => new Handlers\SessionLaravel(),
            'phalcon' => new Handlers\SessionPhalcon(),
            default => throw new InvalidArgumentException(
                'The persistent data handler must be set to "' .
                implode(', ', $handlers) . '" be an instance of ' .
                PersistentDataInterface::class
            ),
        };
    }
}
