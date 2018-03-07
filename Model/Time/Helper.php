<?php

namespace ShoppingFeed\Manager\Model\Time;

use Magento\Framework\Stdlib\DateTime;


class Helper
{
    /**
     * @return int
     */
    public function utcTimestamp()
    {
        return time();
    }

    /**
     * @return string
     */
    public function utcDate()
    {
        return gmdate(DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * @param int $secondsSince
     * @return string
     */
    public function utcPastDate($secondsSince)
    {
        return gmdate(DateTime::DATETIME_PHP_FORMAT, time() - $secondsSince);
    }
}
