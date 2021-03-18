<?php

namespace PhpBench\Expression\ColorMap\Util;

use function array_fill;
use RuntimeException;

final class Gradient
{
    /**
     * @var Color[]
     */
    private $colors;

    private function __construct(Color ...$colors)
    {
        $this->colors = $colors;
    }

    /**
     * @return Color[]
     */
    public function toArray(): array
    {
        return $this->colors;
    }

    public static function start(Color $start): self
    {
        return new self($start);
    }

    public function end(): Color
    {
        return $this->colors[count($this->colors) - 1];
    }

    public function to(Color $end, int $steps): self
    {
        if ($steps <= 0) {
            throw new RuntimeException(sprintf(
                'Number of steps must be more than zero, got "%s"', $steps
            ));
        }

        $gradient = [];
        $start = $this->end()->toTuple();
        $end = $end->toTuple();

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

        $colors = array_merge($this->colors, array_map(function (int $r, int $g, int $b) {
            return Color::fromRgb($r,$g,$b);
        }, ...$gradient));

        // remove the start color as it's already present
        array_shift($colors);

        return new self(...$colors);
    }

    public function colorAtPercentile(int $percentile): Color
    {
        $percentile = $percentile < 0 ? 0 : min(100, abs($percentile));

        $offset = round((count($this->colors) - 1) / 100 * $percentile);

        return $this->colors[$offset];
    }
}
