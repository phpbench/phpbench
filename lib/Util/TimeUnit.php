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
    public const MILLISECOND = 'millisecond';
    public const MICROSECOND = 'microsecond';
    public const SECOND = 'second';
    public const MINUTE = 'minute';
    public const HOUR = 'hour';
    public const DAY = 'day';

    public const MICROSECONDS = 'microseconds';
    public const MILLISECONDS = 'milliseconds';
    public const SECONDS = 'seconds';
    public const MINUTES = 'minutes';
    public const HOURS = 'hours';
    public const DAYS = 'days';

    public const MODE_THROUGHPUT = 'throughput';
    public const MODE_TIME = 'time';
    public const AUTO = 'time';

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

    private static $aliases = [
        self::MICROSECOND => self::MICROSECONDS,
        self::MILLISECOND => self::MILLISECONDS,
        self::SECOND => self::SECONDS,
        self::MINUTE => self::MINUTES,
        self::HOUR => self::HOURS,
        self::DAY => self::DAYS,
        'us' => self::MICROSECONDS,
        'ms' => self::MILLISECONDS,
        's' => self::SECONDS,
        'm' => self::MINUTES,
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

    public function __construct(
        string $sourceUnit = self::MICROSECONDS,
        string $destUnit = self::MICROSECONDS,
        string $mode = self::MODE_TIME,
        int $precision = 3
    ) {
        $this->sourceUnit = $sourceUnit;
        $this->destUnit = $destUnit;
        $this->mode = $mode;
        $this->precision = $precision;
    }

    /**
     * @return string[]
     */
    public static function supportedUnitNames(): array
    {
        return array_merge(
            [
                self::AUTO
            ],
            array_keys(self::$aliases),
            array_keys(self::$map)
        );
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
     */
    public function overrideDestUnit(string $destUnit): void
    {
        $destUnit = self::normalizeUnit($destUnit);
        $this->destUnit = $destUnit;
        $this->overriddenDestUnit = true;
    }

    /**
     * Override the mode.
     *
     */
    public function overrideMode(string $mode): void
    {
        self::validateMode($mode);
        $this->mode = $mode;
        $this->overriddenMode = true;
    }

    /**
     * Override the precision.
     *
     */
    public function overridePrecision(int $precision): void
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
     */
    public function getDestUnit(string $unit = null): string
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
    public function resolveDestUnit($unit, float $value = null)
    {
        if ($unit === self::AUTO) {
            $unit = self::resolveSuitableUnit($value);
        }

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
        if (empty($precision)) {
            return 0;
        }

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
    public function getDestSuffix(string $unit = null, string $mode = null): string
    {
        return self::getSuffix($this->getDestUnit($unit), $this->getMode($mode));
    }

    /**
     * Return a human readable representation of the unit including the suffix.
     */
    public function format(float $time, string $unit = null, string $mode = null, int $precision = null, bool $suffix = true): string
    {
        $value = number_format(
            $this->toDestUnit($time, $unit, $mode),
            $precision !== null ? $precision : $this->precision
        );

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

        $unit = self::normalizeUnit($unit);
        $destUnit = self::normalizeUnit($destUnit);

        $destMultiplier = self::$map[$destUnit];
        $sourceMultiplier = self::$map[$unit];

        $time = $destMultiplier / ($time * $sourceMultiplier);

        return $time;
    }

    /**
     * Convert the given time from the given unit to the given destination
     * unit.
     */
    public static function convertTo(float $time, string $unit, string $destUnit): float
    {
        $unit = self::normalizeUnit($unit);
        $destUnit = self::normalizeUnit($destUnit);

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
     * @return string
     */
    public static function getSuffix(string $unit, string $mode = null)
    {
        $unit = self::normalizeUnit($unit);

        $suffix = self::$suffixes[$unit];

        if ($mode === self::MODE_THROUGHPUT) {
            return sprintf('ops/%s', $suffix);
        }

        return $suffix;
    }

    public static function isTimeUnit(string $unit): bool
    {
        return in_array($unit, self::supportedUnitNames());
    }

    private static function validateMode(string $mode): void
    {
        $validModes = [self::MODE_THROUGHPUT, self::MODE_TIME];

        if (!in_array($mode, $validModes)) {
            throw new \InvalidArgumentException(sprintf(
                'Time mode must be one of "%s", got "%s"',
                implode('", "', $validModes),
                $mode
            ));
        }
    }

    public static function normalizeUnit(string $unit): string
    {
        if (isset(self::$aliases[$unit])) {
            $unit = self::$aliases[$unit];
        }

        if (isset(self::$map[$unit])) {
            return $unit;
        }

        if ($unit === self::AUTO) {
            return $unit;
        }

        throw new \InvalidArgumentException(sprintf(
            'Invalid time unit "%s", available units: "%s"',
            $unit,
            implode('", "', array_keys(self::$map))
        ));
    }

    public static function resolveSuitableUnit(?float $value): string
    {
        if (null === $value) {
            return self::MICROSECONDS;
        }

        if (($value / 60E6) >= 1) {
            return self::MINUTES;
        }

        if (($value / 1E6) >= 1) {
            return self::SECONDS;
        }

        if (($value / 1E3) >= 1) {
            return self::MILLISECONDS;
        }

        return self::MICROSECONDS;
    }
}
