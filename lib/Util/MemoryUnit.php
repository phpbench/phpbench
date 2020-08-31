<?php

namespace PhpBench\Util;

use RuntimeException;

class MemoryUnit
{
    public const BYTES = 'bytes';
    public const KILOBYTES = 'kilobytes';
    public const MEGABYTES = 'megabytes';
    public const GIGABYTES = 'gigabytes';

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

    public static function isMemoryUnit(string $unit): bool
    {
        return isset(self::$multipliers[$unit]) || isset(self::$aliases[$unit]);
    }

    public static function convertTo(float $value, string $fromUnit, string $toUnit): float
    {
        $fromUnit = self::resolveUnit($fromUnit);
        $toUnit = self::resolveUnit($toUnit);
        $byteValue = $value * self::$multipliers[$fromUnit];

        return $byteValue / self::$multipliers[$toUnit];
    }

    private static function resolveUnit(string $unit): string
    {
        if (isset(self::$aliases[$unit])) {
            $unit = self::$aliases[$unit];
        }

        if (!isset(self::$multipliers[$unit])) {
            throw new RuntimeException(sprintf(
                'Unknown memory unit "%s", known memory units: "%s"',
                $unit, implode('", "', array_keys(self::$multipliers))
            ));
        }

        return $unit;
    }
}
