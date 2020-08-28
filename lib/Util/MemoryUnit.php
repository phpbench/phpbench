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

    public static function convertToBytes(float $value, string $unit): int
    {
        if (!isset(self::$multipliers[$unit])) {
            throw new RuntimeException(sprintf(
                'Unknown memory unit "%s", known memory units: "%s"',
                $unit, implode('", "', array_keys(self::$multipliers))
            ));
        }

        return (int) ($value * self::$multipliers[$unit]);
    }
}
