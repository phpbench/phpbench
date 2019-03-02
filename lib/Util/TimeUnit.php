<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
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

    const MODE_THROUGHPUT = 'throughput';
    const MODE_TIME = 'time';

    /**
     * @var array
     */
    private static $map = [
        self::MICROSECONDS => 1,
        self::MILLISECONDS => 1000,
        self::SECONDS => 1000000,
        self::MINUTES => 60000000,
        self::HOURS => 3600000000,
        self::DAYS => 86400000000,
    ];

    /**
     * @var array
     */
    private static $suffixes = [
        self::MICROSECONDS => 'μs',
        self::MILLISECONDS => 'ms',
        self::SECONDS => 's',
        self::MINUTES => 'm',
        self::HOURS => 'h',
        self::DAYS => 'd',
    ];

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
    private $overriddenDestUnit = false;

    /**
     * @var bool
     */
    private $overriddenMode = false;

    /**
     * @var bool
     */
    private $overriddenPrecision = false;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var int
     */
    private $precision;

    public function __construct($sourceUnit = self::MICROSECONDS, $destUnit = self::MICROSECONDS, $mode = self::MODE_TIME, $precision = 3)
    {
        $this->sourceUnit = $sourceUnit;
        $this->destUnit = $destUnit;
        $this->mode = $mode;
        $this->precision = $precision;
    }

    /**
     * Convert instance value to given unit.
     */
    public function toDestUnit(float $time, string $destUnit = null, string $mode = null)
    {
        return self::convert($time, $this->sourceUnit, $this->getDestUnit($destUnit), $this->getMode($mode));
    }

    /**
     * Override the destination unit.
     *
     * @param string $destUnit
     */
    public function overrideDestUnit($destUnit)
    {
        self::validateUnit($destUnit);
        $this->destUnit = $destUnit;
        $this->overriddenDestUnit = true;
    }

    /**
     * Override the mode.
     *
     * @param string $mode
     */
    public function overrideMode($mode)
    {
        self::validateMode($mode);
        $this->mode = $mode;
        $this->overriddenMode = true;
    }

    /**
     * Override the precision.
     *
     * @param int $precision
     */
    public function overridePrecision($precision)
    {
        $this->precision = $precision;
        $this->overriddenPrecision = true;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    /**
     * Return the destination unit.
     *
     * @param string $unit
     *
     * @return string
     */
    public function getDestUnit($unit = null)
    {
        // if a unit is given, use that
        if ($unit) {
            return $unit;
        }

        // otherwise return the default
        return $this->destUnit;
    }

    /**
     * Utility method, if the dest unit is overridden, return the overridden
     * value.
     *
     * @return string
     */
    public function resolveDestUnit($unit)
    {
        if ($this->overriddenDestUnit) {
            return $this->destUnit;
        }

        return $unit;
    }

    /**
     * Utility method, if the mode is overridden, return the overridden
     * value.
     *
     * @return string
     */
    public function resolveMode($mode)
    {
        if ($this->overriddenMode) {
            return $this->mode;
        }

        return $mode;
    }

    /**
     * Utility method, if the precision is overridden, return the overridden
     * value.
     */
    public function resolvePrecision($precision): ?int
    {
        if ($this->overriddenPrecision) {
            return $this->precision;
        }

        return $precision;
    }

    /**
     * Return the destination mode.
     */
    public function getMode(string $mode = null): string
    {
        // if a mode is given, use that
        if ($mode) {
            return $mode;
        }

        // otherwise return the default
        return $this->mode;
    }

    /**
     * Return the destination unit suffix.
     */
    public function getDestSuffix(string $unit = null, string $mode = null)
    {
        return self::getSuffix($this->getDestUnit($unit), $this->getMode($mode));
    }

    /**
     * Return a human readable representation of the unit including the suffix.
     */
    public function format(float $time, string $unit = null, string $mode = null, int $precision = null, bool $suffix = true)
    {
        $value = number_format($this->toDestUnit($time, $unit, $mode), $precision !== null ? $precision : $this->precision);

        if (false === $suffix) {
            return $value;
        }

        $suffix = $this->getDestSuffix($unit, $mode);

        return $value . $suffix;
    }

    /**
     * Convert given time in given unit to given destination unit in given mode.
     */
    public static function convert(float $time, string $unit, string $destUnit, string $mode)
    {
        self::validateMode($mode);

        if ($mode === self::MODE_TIME) {
            return self::convertTo($time, $unit, $destUnit);
        }

        return self::convertInto($time, $unit, $destUnit);
    }

    /**
     * Convert a given time INTO the given unit. That is, how many times the
     * given time will fit into the the destination unit. i.e. `x` per unit.
     */
    public static function convertInto(float $time, string $unit, string $destUnit)
    {
        if (!$time) {
            return 0;
        }

        self::validateUnit($unit);
        self::validateUnit($destUnit);

        $destMultiplier = self::$map[$destUnit];
        $sourceMultiplier = self::$map[$unit];

        $time = $destMultiplier / ($time * $sourceMultiplier);

        return $time;
    }

    /**
     * Convert the given time from the given unit to the given destination
     * unit.
     */
    public static function convertTo(float $time, string $unit, string $destUnit)
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
     * @param string $unit
     * @param string $mode
     *
     * @return string
     */
    public static function getSuffix($unit, $mode = null)
    {
        self::validateUnit($unit);

        $suffix = self::$suffixes[$unit];

        if ($mode === self::MODE_THROUGHPUT) {
            return sprintf('ops/%s', $suffix);
        }

        return $suffix;
    }

    private static function validateUnit($unit)
    {
        if (!isset(self::$map[$unit])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid time unit "%s", available units: "%s"',
                $unit, implode('", "', array_keys(self::$map))
            ));
        }
    }

    private static function validateMode($mode)
    {
        $validModes = [self::MODE_THROUGHPUT, self::MODE_TIME];

        if (!in_array($mode, $validModes)) {
            throw new \InvalidArgumentException(sprintf(
                'Time mode must be one of "%s", got "%s"',
                implode('", "', $validModes), $mode
            ));
        }
    }
}
