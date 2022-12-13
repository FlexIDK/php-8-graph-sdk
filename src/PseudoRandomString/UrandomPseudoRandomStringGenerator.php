<?php

namespace One23\GraphSdk\PseudoRandomString;

use One23\GraphSdk\Exceptions\FacebookSDKException;

class UrandomPseudoRandomStringGenerator implements PseudoRandomStringGeneratorInterface
{

    use PseudoRandomStringGeneratorTrait;

    /**
     * @const string The error message when generating the string fails.
     */
    const ERROR_MESSAGE = 'Unable to generate a cryptographically secure pseudo-random string from /dev/urandom. ';

    /**
     * @throws FacebookSDKException
     */
    public function __construct()
    {
        if (ini_get('open_basedir')) {
            throw new FacebookSDKException(
                static::ERROR_MESSAGE .
                'There is an open_basedir constraint that prevents access to /dev/urandom.'
            );
        }

        if (!is_readable('/dev/urandom')) {
            throw new FacebookSDKException(
                static::ERROR_MESSAGE .
                'Unable to read from /dev/urandom.'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getPseudoRandomString($length)
    {
        $this->validateLength($length);

        $stream = fopen('/dev/urandom', 'rb');
        if (!is_resource($stream)) {
            throw new FacebookSDKException(
                static::ERROR_MESSAGE .
                'Unable to open stream to /dev/urandom.'
            );
        }

        if (!defined('HHVM_VERSION')) {
            stream_set_read_buffer($stream, 0);
        }

        $binaryString = fread($stream, $length);
        fclose($stream);

        if (!$binaryString) {
            throw new FacebookSDKException(
                static::ERROR_MESSAGE .
                'Stream to /dev/urandom returned no data.'
            );
        }

        return $this->binToHex($binaryString, $length);
    }
}
