<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Util;

/**
 * Utility class for representing and converting time units.
 */
class TimeUnit
{
    const MICROSECONDS = 'microseconds';
    const MILLISECONDS = 'milliseconds';
    const SECONDS = 'seconds';
    const MINUTES = 'minutes';
    const HOURS = 'hours';
    const DAYS = 'days';

    /**
     * @var array
     */
    private static $map = array(
        self::MICROSECONDS => 1,
        self::MILLISECONDS => 1000,
        self::SECONDS      => 1000000,
        self::MINUTES      => 60000000,
        self::HOURS        => 3600000000,
        self::DAYS         => 86400000000,
    );

    private static $suffixes = array(
        self::MICROSECONDS => 'μs',
        self::MILLISECONDS => 'ms',
        self::SECONDS      => 's',
        self::MINUTES      => 'm',
        self::HOURS        => 'h',
        self::DAYS         => 'd',
    );

    /**
     * @var string
     */
    private $sourceUnit;

    /**
     * @var string
     */
    private $destUnit;

    /**
     * @var bool
     */
    private $overridden = false;

    public function __construct($sourceUnit, $destUnit)
    {
        $this->sourceUnit = $sourceUnit;
        $this->destUnit = $destUnit;
    }

    /**
     * Convert instance value to given unit.
     *
     * @param string
     * @return int
     */
    public function toDestUnit($time, $destUnit = null)
    {
        return self::convertTo($time, $this->sourceUnit, $destUnit ?: $this->destUnit);
    }

    /**
     * Convert time to number of source units per destination unit
     *
     * @param integer
     * @return int
     */
    public function intoDestUnit($time, $destUnit = null)
    {
        return self::convertInto($time, $this->sourceUnit, $destUnit ?: $this->destUnit);
    }

    /**
     * Override the destination unit.
     *
     * @param string
     */
    public function overrideDestUnit($destUnit)
    {
        self::validateUnit($destUnit);
        $this->destUnit = $destUnit;
        $this->overridden = true;
    }

    /**
     * Return true if the destination unit has been overridden.
     *
     * @return bool
     */
    public function isOverridden()
    {
        return $this->overridden;
    }

    /**
     * Return the destination unit.
     *
     * @return string
     */
    public function getDestUnit()
    {
        return $this->destUnit;
    }

    /**
     * Static conversion method.
     *
     * @static
     *
     * @param int
     * @param string
     * @param string
     * @return int
     */
    public static function convertInto($time, $unit, $destUnit)
    {
        self::validateUnit($unit);
        self::validateUnit($destUnit);

        $destM = self::$map[$destUnit];
        $sourceM = self::$map[$unit];

        $time = $destM / ($time * $sourceM); 

        return $time;
    }

    /**
     *
     * @static
     *
     * @param int
     * @param string
     * @param string
     * @return int
     */
    public static function convertTo($time, $unit, $destUnit)
    {
        self::validateUnit($unit);
        self::validateUnit($destUnit);

        $destM = self::$map[$destUnit];
        $sourceM = self::$map[$unit];

        $time = ($time * $sourceM) / $destM;

        return $time;
    }

    /**
     * Return the suffix for a given unit.
     *
     * @static
     *
     * @param string
     * @return string
     */
    public static function getSuffix($unit)
    {
        self::validateUnit($unit);

        return self::$suffixes[$unit];
    }

    /**
     * Return the destination unit suffix.
     *
     * @return string
     */
    public function getDestSuffix()
    {
        return self::getSuffix($this->destUnit);
    }

    private static function validateUnit($unit)
    {
        if (!is_string($unit)) {
            throw new \InvalidArgumentException(sprintf(
                'Expected string value for time unit, got "%s"',
                is_object($unit) ? get_class($unit) : gettype($unit)
            ));
        }
        if (!isset(self::$map[$unit])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid time unit "%s", available units: "%s"',
                $unit, implode('", "', array_keys(self::$map))
            ));
        }
    }
}
