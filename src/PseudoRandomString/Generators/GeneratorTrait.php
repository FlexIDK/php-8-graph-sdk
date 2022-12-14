<?php

namespace One23\GraphSdk\PseudoRandomString\Generators;

trait GeneratorTrait
{
    /**
     * Validates the length argument of a random string.
     *
     * @throws \InvalidArgumentException
     */
    public function validateLength(int $length): void
    {
        if ($length < 1) {
            throw new \InvalidArgumentException('getPseudoRandomString() expects a length greater than 1');
        }
    }

    /**
     * Converts binary data to hexadecimal of arbitrary length.
     */
    public function binToHex(string $binaryData, int $length): string
    {
        return \substr(\bin2hex($binaryData), 0, $length);
    }
}
