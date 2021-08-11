<?php

namespace PhpBench\Util;

use RuntimeException;

class MemoryUnit
{
    public const BYTES = 'bytes';
    public const KILOBYTES = 'kilobytes';
    public const MEGABYTES = 'megabytes';
    public const GIGABYTES = 'gigabytes';
    public const AUTO = 'memory';

    /**
     * @var array<string, int>
     */
    private static $multipliers = [
        self::BYTES => 1,
        self::KILOBYTES => 1000,
        self::MEGABYTES => 1000000,
        self::GIGABYTES => 1000000000,
    ];

    private static $aliases = [
        'b' => self::BYTES,
        'k' => self::KILOBYTES,
        'kb' => self::KILOBYTES,
        'mb' => self::MEGABYTES,
        'gb' => self::GIGABYTES
    ];

    private static $suffixes = [
        self::BYTES => 'b',
        self::KILOBYTES => 'kb',
        self::MEGABYTES => 'mb',
        self::GIGABYTES => 'gb',
    ];

    /**
     * @return string[]
     */
    public static function supportedUnitNames(): array
    {
        return array_merge(
            [
                self::AUTO,
            ],
            array_keys(self::$aliases),
            array_keys(self::$multipliers)
        );
    }

    public static function suffixFor(string $unit): string
    {
        $unit = self::resolveUnit($unit);

        return isset(self::$suffixes[$unit]) ? self::$suffixes[$unit] : $unit;
    }

    public static function isMemoryUnit(string $unit): bool
    {
        return in_array($unit, self::supportedUnitNames());
    }

    public static function convertTo(float $value, string $fromUnit, string $toUnit): float
    {
        $fromUnit = self::resolveUnit($fromUnit);
        $toUnit = self::resolveUnit($toUnit);
        $byteValue = $value * self::$multipliers[$fromUnit];

        return $byteValue / self::$multipliers[$toUnit];
    }

    public static function resolveSuitableUnit(?string $unit, ?float $value): string
    {
        if ($unit !== self::AUTO) {
            return $unit;
        }

        if (null === $value) {
            return self::BYTES;
        }

        if (($value / 1E9) >= 1) {
            return self::GIGABYTES;
        }

        if (($value / 1E6) >= 1) {
            return self::MEGABYTES;
        }

        if (($value / 1E3) >= 1) {
            return self::KILOBYTES;
        }

        return self::BYTES;
    }

    private static function resolveUnit(string $unit): string
    {
        if (isset(self::$aliases[$unit])) {
            $unit = self::$aliases[$unit];
        }

        if (!isset(self::$multipliers[$unit])) {
            throw new RuntimeException(sprintf(
                'Unknown memory unit "%s", known memory units: "%s"',
                $unit,
                implode('", "', array_keys(self::$multipliers))
            ));
        }

        return $unit;
    }
}
