<?php

namespace PhpBench\Util;

use RuntimeException;

class MemoryUnit
{
    final public const BYTES = 'bytes';
    final public const KILOBYTES = 'kilobytes';
    final public const MEGABYTES = 'megabytes';
    final public const GIGABYTES = 'gigabytes';
    final public const KIBIBYTES = 'kibibytes';
    final public const MEBIBYTES = 'mebibytes';
    final public const GIBIBYTES = 'gibibytes';
    final public const AUTO = 'memory';

    /**
     * @var array<string, int>
     */
    private static array $multipliers = [
        self::BYTES => 1,
        self::KILOBYTES => 1000,
        self::MEGABYTES => 1_000_000,
        self::GIGABYTES => 1_000_000_000,
        self::KIBIBYTES => 1024,
        self::MEBIBYTES => 1_048_576,
        self::GIBIBYTES => 1_073_741_824,
    ];

    /** @var array<string, string> */
    private static array $aliases = [
        'b' => self::BYTES,
        'k' => self::KILOBYTES,
        'kb' => self::KILOBYTES,
        'mb' => self::MEGABYTES,
        'gb' => self::GIGABYTES,
        'kib' => self::KIBIBYTES,
        'mib' => self::MEBIBYTES,
        'gib' => self::GIBIBYTES
    ];

    /** @var array<string, string> */
    private static array $suffixes = [
        self::BYTES => 'b',
        self::KILOBYTES => 'kb',
        self::MEGABYTES => 'mb',
        self::GIGABYTES => 'gb',
        self::KIBIBYTES => 'KiB',
        self::MEBIBYTES => 'MiB',
        self::GIBIBYTES => 'GiB'
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

        return self::$suffixes[$unit] ?? $unit;
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

    /**
     * Resolve a binary unit
     */
    public static function resolveSuitableBinaryUnit(string $unit, ?float $value): string
    {
        if ($unit !== self::AUTO) {
            return $unit;
        }

        if (null === $value) {
            return self::BYTES;
        }

        if (($value / 1E9) >= 1) {
            return self::GIBIBYTES;
        }

        if (($value / 1E6) >= 1) {
            return self::MEBIBYTES;
        }

        if (($value / 1E3) >= 1) {
            return self::KIBIBYTES;
        }

        return self::BYTES;
    }

    /**
     * Resolve an SI unit
     */
    public static function resolveSuitableUnit(string $unit, ?float $value): string
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
