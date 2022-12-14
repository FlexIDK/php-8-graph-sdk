<?php

namespace One23\GraphSdk\PseudoRandomString\Generators;

use \One23\GraphSdk\Exceptions;

interface GeneratorInterface
{
    /**
     * Get a cryptographically secure pseudo-random string of arbitrary length.
     *
     * @see http://sockpuppet.org/blog/2014/02/25/safely-generate-random-numbers/
     *
     * @throws Exceptions\SDKException|\InvalidArgumentException
     */
    public function getPseudoRandomString(int $length): string;
}
