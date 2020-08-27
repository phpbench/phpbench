<?php

namespace PhpBench\Math;

class FloatNumber
{
    public static function isLessThanOrEqual(float $first, float $second)
    {
        if ((string)$first === (string)$second) {
            return true;
        }

        return $first < $second;
    }
}
