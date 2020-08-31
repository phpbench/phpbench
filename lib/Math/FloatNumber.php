<?php

namespace PhpBench\Math;

class FloatNumber
{
    public static function isLessThanOrEqual(float $left, float $right): bool
    {
        if (self::isEqual($left, $right)) {
            return true;
        }

        return $left < $right;
    }

    public static function isEqual(float $left, float $right): bool
    {
        return (string)$left === (string)$right;
    }

    public static function isWithin(float $number, float $lower, float $upper): bool
    {
        return self::isGreaterThanOrEqual($number, $lower) && self::isLessThanOrEqual($number, $upper);
    }

    public static function isGreaterThanOrEqual(float $left, float $right): bool
    {
        if (self::isEqual($left, $right)) {
            return true;
        }

        return $left > $right;
    }
}
