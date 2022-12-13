<?php

namespace One23\GraphSdk\PseudoRandomString;

use One23\GraphSdk\Exceptions\FacebookSDKException;

class McryptPseudoRandomStringGenerator implements PseudoRandomStringGeneratorInterface
{
    use PseudoRandomStringGeneratorTrait;

    /**
     * @const string The error message when generating the string fails.
     */
    const ERROR_MESSAGE = 'Unable to generate a cryptographically secure pseudo-random string from mcrypt_create_iv(). ';

    /**
     * @throws FacebookSDKException
     */
    public function __construct()
    {
        if (!function_exists('mcrypt_create_iv')) {
            throw new FacebookSDKException(
                static::ERROR_MESSAGE .
                'The function mcrypt_create_iv() does not exist.'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getPseudoRandomString($length)
    {
        $this->validateLength($length);

        try {
            $binaryString = random_bytes($length);
        }
        catch (\Exception $exception) {
            throw new FacebookSDKException(
                static::ERROR_MESSAGE .
                'random_bytes() returned an error.'
            );
        }

        return $this->binToHex($binaryString, $length);
    }
}
