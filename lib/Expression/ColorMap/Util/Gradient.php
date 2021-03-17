<?php

namespace PhpBench\Expression\ColorMap\Util;

use RuntimeException;
use function array_fill;
use function dechex;
use function sscanf;

class Gradient
{
    /**
     * @return Color[]
     */
    public function gradient(Color $start, Color $end, int $steps): array
    {
        if ($steps <= 0) {
            throw new RuntimeException(sprintf(
                'Number of steps must be more than zero, got "%s"', $steps
            ));
        }

        $gradient = [];
        $start = $start->toTuple();
        $end= $end->toTuple();

        for ($i = 0; $i <= 2; $i++) {
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
            return new Color($r,$g,$b);
        }, ...$gradient);
    }
}
