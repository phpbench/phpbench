<?php

namespace PhpBench\Color;

use PhpBench\Expression\Theme\Util\Color as DeprecatedColor;
use RuntimeException;

use function array_fill;

/** final */ class Gradient
{
    /**
     * @var Color[]
     */
    private $colors;

    final private function __construct(Color ...$colors)
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

    /**
     * @return static
     */
    public static function start(Color $start)
    {
        return new static($start);
    }

    public function end(): Color
    {
        return $this->colors[count($this->colors) - 1];
    }

    /**
     * @return static
     */
    public function to(Color $end, int $steps)
    {
        if ($steps <= 0) {
            throw new RuntimeException(sprintf(
                'Number of steps must be more than zero, got "%s"',
                $steps
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

        /** @phpstan-ignore-next-line */
        $colors = array_merge($this->colors, array_map(function (float $r, float $g, float $b) {
            return DeprecatedColor::fromRgb((int)$r, (int)$g, (int)$b);
        }, ...$gradient));

        // remove the start color as it's already present
        array_shift($colors);

        return new static(...$colors);
    }

    public function colorAtPercentile(float $percentile): Color
    {
        $percentile = $percentile < 0 ? 0 : min(100, abs($percentile));

        $offset = round((count($this->colors) - 1) / 100 * $percentile);

        return $this->colors[$offset];
    }
}
