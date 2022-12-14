<?php

namespace One23\GraphSdk\PseudoRandomString;

use One23\GraphSdk\Exceptions\SDKException;
use InvalidArgumentException;

class GeneratorFactory
{
    private function __construct()
    {
    }

    /**
     * Pseudo random string generator creation.
     *
     * @throws InvalidArgumentException
     */
    public static function createPseudoRandomStringGenerator(Generators\GeneratorInterface|string $generator = null): Generators\GeneratorInterface
    {
        if (!$generator) {
            return self::detectDefaultPseudoRandomStringGenerator();
        }

        if ($generator instanceof Generators\GeneratorInterface) {
            return $generator;
        }

        return match ($generator) {
            'random_bytes' => new Generators\RandomBytesGenerator(),
            'mcrypt' => new Generators\McryptGenerator(),
            'openssl' => new Generators\OpenSslGenerator(),
            'urandom' => new Generators\UrandomGenerator(),
            default => throw new InvalidArgumentException('The pseudo random string generator must be set to "random_bytes", "mcrypt", "openssl", or "urandom", or be an instance of ' . Generators\GeneratorInterface::class),
        };
    }

    /**
     * Detects which pseudo-random string generator to use.
     *
     * @throws SDKException
     */
    private static function detectDefaultPseudoRandomStringGenerator(): Generators\GeneratorInterface
    {
        // Check for PHP 7's CSPRNG first to keep mcrypt deprecation messages from appearing in PHP 7.1.
        if (function_exists('random_bytes')) {
            return new Generators\RandomBytesGenerator();
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            return new Generators\OpenSslGenerator();
        }

        if (!ini_get('open_basedir') && is_readable('/dev/urandom')) {
            return new Generators\UrandomGenerator();
        }

        throw new SDKException('Unable to detect a cryptographically secure pseudo-random string generator.');
    }
}
