<?php

namespace One23\GraphSdk\GraphNodes;

use One23\GraphSdk\Exception;

class Birthday extends \DateTime
{
    private bool $hasDate = false;

    private bool $hasYear = false;

    /**
     * Parses Graph birthday format to set indication flags, possible values:
     *
     *  MM/DD/YYYY
     *  MM/DD
     *  YYYY
     *
     * @link https://developers.facebook.com/docs/graph-api/reference/user
     *
     * @throws Exception
     */
    public function __construct(string $date)
    {
        $parts = explode('/', $date);

        $this->hasYear = count($parts) === 3 || count($parts) === 1;
        $this->hasDate = count($parts) === 3 || count($parts) === 2;

        try {
            parent::__construct($date);
        }
        catch (\Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Returns whether date object contains birthday and month
     */
    public function hasDate(): bool
    {
        return $this->hasDate;
    }

    /**
     * Returns whether date object contains birth year
     */
    public function hasYear(): bool
    {
        return $this->hasYear;
    }
}
