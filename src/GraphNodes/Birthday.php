<?php

namespace One23\GraphSdk\GraphNodes;

use DateTime;

/**
 * Birthday object to handle various Graph return formats

 */
class Birthday extends DateTime
{
    /**
     * @var bool
     */
    private $hasDate = false;

    /**
     * @var bool
     */
    private $hasYear = false;

    /**
     * Parses Graph birthday format to set indication flags, possible values:
     *
     *  MM/DD/YYYY
     *  MM/DD
     *  YYYY
     *
     * @link https://developers.facebook.com/docs/graph-api/reference/user
     *
     * @param string $date
     */
    public function __construct($date)
    {
        $parts = explode('/', $date);

        $this->hasYear = count($parts) === 3 || count($parts) === 1;
        $this->hasDate = count($parts) === 3 || count($parts) === 2;

        parent::__construct($date);
    }

    /**
     * Returns whether date object contains birth day and month
     *
     * @return bool
     */
    public function hasDate()
    {
        return $this->hasDate;
    }

    /**
     * Returns whether date object contains birth year
     *
     * @return bool
     */
    public function hasYear()
    {
        return $this->hasYear;
    }
}
