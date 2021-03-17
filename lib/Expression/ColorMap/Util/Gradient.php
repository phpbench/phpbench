<?php

namespace PhpBench\Expression\ColorMap\Util;

use RuntimeException;
use function array_fill;
use function dechex;
use function sscanf;

class Gradient
{
    public function colorizeSignedPercentage(string $start, string $end, int $value)
    {

    }

    /**
     * @param (array{string,string})[] $ranges
     * @return string[]
     */
    public function hexGradients(array $ranges, int $steps): array
    {
        return array_merge(...array_map(function (array $range) use ($steps) {
            return $this->hexGradient($range[0], $range[1], $steps);
        }, $ranges));
    }

    /**
     * @return string[]
     */
    public function hexGradient(string $start, string $end, int $steps): array
    {
        $start = $this->hexToRgb($start);
        $end = $this->hexToRgb($end);

        return array_map(function (array $color) {
            return implode('', array_map(function (int $number) {
                return sprintf('%02s', dechex($number));
            }, $color));
        }, $this->rgbGradient($start, $end, $steps));
    }

    /**
     * @param array{int,int,int} $start
     * @param array{int,int,int} $end
     * @return (array{int,int,int})[]
     */
    private function rgbGradient(array $start, array $end, int $steps): array
    {
        if ($steps <= 0) {
            throw new RuntimeException(sprintf(
                'Number of steps must be more than zero, got "%s"', $steps
            ));
        }

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

    /**
     * @return array{int,int,int}
     */
    private function hexToRgb(string $start): array
    {
        $hexRgb = sscanf(ltrim($start, '#'), '%02s%02s%02s');
        if (!is_array($hexRgb)) {
            throw new RuntimeException(sprintf(
                'Could not parse hex color "%s"', $start
            ));
        }

        /** @phpstan-ignore-next-line */
        return array_map('hexdec', $hexRgb);
    }
}
