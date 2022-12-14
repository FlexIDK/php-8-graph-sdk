<?php

namespace One23\GraphSdk\PseudoRandomString\Generators;

use One23\GraphSdk\Exceptions\SDKException;

class RandomBytesGenerator implements GeneratorInterface
{
    use GeneratorTrait;

    const ERROR_MESSAGE = 'Unable to generate a cryptographically secure pseudo-random string from random_bytes(). ';

    /**
     * @throws SDKException
     */
    public function __construct()
    {
        if (!function_exists('random_bytes')) {
            throw new SDKException(
                static::ERROR_MESSAGE .
                'The function random_bytes() does not exist.'
            );
        }
    }

    public function getPseudoRandomString(int $length): string
    {
        $this->validateLength($length);

        try {
            $binaryString = random_bytes($length);
        }
        catch (\Exception $exception) {
            throw new SDKException(
                static::ERROR_MESSAGE .
                'random_bytes() returned an error.'
            );
        }

        return $this->binToHex($binaryString, $length);
    }
}
