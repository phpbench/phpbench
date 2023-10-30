<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Math;

/**
 * Static class containing functions related to statistics.
 */
class Statistics
{
    /**
     * Return the standard deviation of a given population.
     *
     */
    public static function stdev(array $values, bool $sample = false): float
    {
        $variance = self::variance($values, $sample);

        return \sqrt($variance);
    }

    /**
     * Return the variance for a given population.
     *
     *
     * @return float
     */
    public static function variance(array $values, bool $sample = false)
    {
        $average = self::mean($values);
        $sum = 0;

        foreach ($values as $value) {
            $diff = pow($value - $average, 2);
            $sum += $diff;
        }

        if (count($values) === 0) {
            return 0;
        }

        $variance = $sum / (count($values) - ($sample ? 1 : 0));

        return $variance;
    }

    /**
     * Return the mean (average) value of the given values.
     *
     *
     */
    public static function mean(array $values)
    {
        if (empty($values)) {
            return 0;
        }

        $sum = array_sum($values);

        if (0 == $sum) {
            return 0;
        }

        $count = count($values);

        return $sum / $count;
    }

    /**
     * Return the mode using the kernel density estimator using the normal
     * distribution.
     *
     * The mode is the point in the kernel density estimate with the highest
     * frequency, i.e. the time which correlates with the highest peak.
     *
     * If there are two or more modes (i.e. bimodal, trimodal, etc) then we
     * could take the average of these modes.
     *
     * NOTE: If the kde estimate of the population is multi-modal (it has two
     * points with exactly the same value) then the mean mode is returned. This
     * is potentially misleading, but When benchmarking this should be a very
     * rare occurance.
     *
     */
    public static function kdeMode(array $population, int $space = 512, string $bandwidth = null): float
    {
        if (count($population) === 1) {
            return current($population);
        }

        if (count($population) === 0) {
            return 0.0;
        }

        if (min($population) == max($population)) {
            return min($population);
        }

        $kde = new Kde($population, $bandwidth);
        $space = self::linspace(min($population), max($population), $space, true);
        $dist = $kde->evaluate($space);

        $maxKeys = array_keys($dist, max($dist));
        $modes = [];

        foreach ($maxKeys as $maxKey) {
            $modes[] = $space[$maxKey];
        }

        $mode = array_sum($modes) / count($modes);

        return $mode;
    }

    /**
     * Return an array populated with $num numbers from $min to $max.
     *
     *
     * @return float[]
     */
    public static function linspace(float $min, float $max, int $num = 50, bool $endpoint = true): array
    {
        $range = $max - $min;

        if ($max == $min) {
            throw new \InvalidArgumentException(sprintf(
                'Min and max cannot be the same number: %s',
                $max
            ));
        }

        $unit = $range / ($endpoint ? $num - 1 : $num);
        $space = [];

        for ($value = $min; $value <= $max; $value += $unit) {
            $space[] = $value;
        }

        if ($endpoint === false) {
            array_pop($space);
        }

        return $space;
    }

    /**
     * Generate a histogram.
     *
     * Note this is not a great function, and should not be relied upon
     * for serious use.
     *
     * For a better implementation copy:
     *   http://docs.scipy.org/doc/numpy-1.10.1/reference/generated/numpy.histogram.html
     *
     */
    public static function histogram(array $values, int $steps = 10, float $lowerBound = null, float $upperBound = null): array
    {
        $min = $lowerBound ?: min($values);
        $max = $upperBound ?: max($values);

        $range = $max - $min;

        $step = $range / $steps;
        $steps++; // add one extra step to catch the max value

        $histogram = [];

        $floor = $min;

        for ($i = 0; $i < $steps; $i++) {
            $ceil = $floor + $step;

            if (!isset($histogram[(string) $floor])) {
                $histogram[(string) $floor] = 0;
            }

            foreach ($values as $value) {
                if ($value >= $floor && $value < $ceil) {
                    $histogram[(string) $floor]++;
                }
            }

            $floor += $step;
            $ceil += $step;
        }

        return $histogram;
    }

    public static function percentageDifference(float $value1, float $value2): float
    {
        if ($value1 == 0 && $value2 == 0) {
            return 0;
        }

        if ($value1 == 0) {
            return INF;
        }

        return (($value2 / $value1) - 1) * 100;
    }

    /**
     * @param (int|float)[] $values
     */
    public static function rstdev(array $values, bool $sample = false): float
    {
        $mean = self::mean($values);

        return $mean ? self::stdev($values, $sample) / $mean * 100 : 0;
    }
}
