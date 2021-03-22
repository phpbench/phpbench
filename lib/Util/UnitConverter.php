<?php

namespace PhpBench\Util;

use RuntimeException;

class UnitConverter
{
    public const MILLISECOND = 'millisecond';
    public const MICROSECOND = 'microsecond';
    public const SECOND = 'second';
    public const MINUTE = 'minute';
    public const HOUR = 'hour';
    public const DAY = 'day';

    public const BYTE = 'byte';
    public const KILOBYTE = 'kilobyte';
    public const MEGABYTE = 'megabyte';
    public const GIGABYTE = 'gigabyte';

    /**
     * @var array<string,int>
     */
    private static $map = [
        self::MICROSECOND => 1,
        self::MILLISECOND => 1000,
        self::SECOND => 1000000,
        self::MINUTE => 60000000,
        self::HOUR => 3600000000,
        self::DAY => 86400000000,

        self::BYTE => 1,
        self::KILOBYTE => 1000,
        self::MEGABYTE => 1000000,
        self::GIGABYTE => 1000000000,
    ];

    /**
     * @var array<string,string>
     */
    private static $aliases = [
        'microseconds' => self::MICROSECOND,
        'milliseconds' => self::MILLISECOND,
        'seconds' => self::SECOND,
        'minutes' => self::MINUTE,
        'hours' => self::HOUR,
        'days' => self::DAY,
        'us' => self::MICROSECOND,
        'ms' => self::MILLISECOND,
        's' => self::SECOND,
        'm' => self::MINUTE,
        'b' => self::BYTE,
        'bytes' => self::BYTE,
        'k' => self::KILOBYTE,
        'kilobytes' => self::KILOBYTE,
        'gigabytes' => self::GIGABYTE,
        'kb' => self::KILOBYTE,
        'mb' => self::MEGABYTE,
        'gb' => self::GIGABYTE
    ];

    /**
     * @var array<string,string>
     */
    private static $suffixes = [
        self::MICROSECOND => 'μs',
        self::MILLISECOND => 'ms',
        self::SECOND => 's',
        self::MINUTE => 'm',
        self::HOUR => 'h',
        self::DAY => 'd',

        self::BYTE => 'b',
        self::KILOBYTE => 'kb',
        self::MEGABYTE => 'mb',
        self::GIGABYTE => 'gb',
    ];

    /**
     * @return string[]
     */
    public static function supportedUnits(): array
    {
        return array_merge(
            array_keys(self::$map),
            array_keys(self::$aliases)
        );
    }

    public static function convert(string $from, string $to, float $value): UnitValue
    {
        return new UnitValue(
            ($value * self::multiplier($from)) / self::multiplier($to),
            self::suffix(self::normalizeUnit($to))
        );
    }

    public static function suffix(string $to): string
    {
        $to = self::normalizeUnit($to);

        if (!isset(self::$suffixes[$to])) {
            throw new RuntimeException(sprintf(
                'No suffix available for "%s"',
                $to
            ));
        }

        return self::$suffixes[$to];
    }

    private static function multiplier(string $unit): float
    {
        return self::$map[self::normalizeUnit($unit)];
    }

    private static function normalizeUnit(string $unit): string
    {
        if (isset(self::$map[$unit])) {
            return $unit;
        }

        if (isset(self::$aliases[$unit])) {
            return self::$aliases[$unit];
        }

        throw new RuntimeException(sprintf(
            'Unknown unit "%s", known units: "%s"',
            $unit,
            implode('", "', self::supportedUnits())
        ));
    }
}
