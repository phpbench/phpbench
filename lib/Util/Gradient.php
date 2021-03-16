<?php

namespace PhpBench\Util;

use function array_fill;
use function dechex;
use function sscanf;

class Gradient
{
    /**
     * @return string[]
     */
    public static function calculateHex(string $start, string $end, int $steps): array
    {
        $start = array_map('hexdec', sscanf(ltrim($start, '#'), '%02s%02s%02s'));
        $end = array_map('hexdec', sscanf(ltrim($end, '#'), '%02s%02s%02s'));

        return array_map(function (array $color) {
            return implode('', array_map(function (int $number) {
                return sprintf('%02s', dechex($number));
            }, $color));
        }, self::calculateRgb($start, $end, $steps));
    }

    /**
     * @param array<int,int,int> $start
     * @param array<int,int,int> $end
     * @return (array<int,int,int>)[]
     */
    public static function calculateRgb(array $start, array $end, int $steps): array
    {
        $gradient = [];
        for ($i = 0; $i < 3; $i++) {
            $cStart = $start[$i];
            $cEnd = $end[$i];
            $step = abs($cStart - $cEnd) / $steps;
            if ($step === 0) {
                $gradient[$i] = array_fill(0, $steps + 1, $cStart);
                continue;
            }
            $gradient[$i] = range($cStart, $cEnd, $step);
        }

        return array_map(function (int $r, int $g, int $b) {
            return [$r,$g,$b];
        }, ...$gradient);
    }
}
