<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @param \DOMNodeList $nodeList
     * @param bool $sample
     *
     * @return float
     */
    public static function stdev(array $values, $sample = false)
    {
        $variance = self::variance($values, $sample);

        return \sqrt($variance);
    }

    /**
     * Return the variance for a given population.
     *
     * @param array $values
     * @param bool $sample
     *
     * @return float
     */
    public static function variance(array $values, $sample = false)
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
     * @param array $values
     *
     * @return mixed
     */
    public static function mean($values)
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
     * Generate a histogram
     */
    public static function histogram(array $values, $bins = 10, $lowerBound = null, $upperBound = null)
    {
        $min = $lowerBound ?: min($values);
        $max = $upperBound ?: max($values);

        $range = $max - $min;

        $step = $range / $bins;
        $bins++; // add one extra step to catch the max value

        $histogram = array();

        $floor = $min;
        for ($i = 0; $i < $bins; $i++) {
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

    /**
     * Calculate the normal probability density function.
     *
     * f(x, μ, σ) = 1 / (σ * sqrt(2 * 𝚷 ) * exp(-((x - μ) ^ 2) / (2 * (σ ^ 2)))
     *
     * https://en.wikipedia.org/wiki/Standard_normal
     *
     * @param float $xValue
     * @param float $mean
     * @param float $standardDeviation
     *
     * @return float
     */
    public static function pdfNormal($xValue, $mean, $stDev) 
    {
        return 
            1 / ($stDev * sqrt(2 * M_PI))
            * 
            exp(
                -(
                    pow($xValue - $mean, 2) /  (2 * pow($stDev, 2))
                )
            );
    }

    /**
     * Calculate the kernel desensity using the normal probability
     * density function.
     *
     */
    public static function kdeNormal(array $population, $bandwidth = 0.5)
    {
        $xMin = min($population);
        $xMax = max($population);
        $xValues = self::linspace($xMin, $xMax, 100);
        $yValues = array_fill(0, count($xValues), 0);

        $counter = 0;
        foreach ($xValues as $xValue) {

            $sum = 0;
            foreach ($population as $sample) {
                $sum += self::pdfNormal(
                    ($xValue - $sample) / $bandwidth,
                    0,
                    Statistics::stdev($population)
                );
            }
            $yValues[$counter] = $sum / (count($population) * $bandwidth);
            $counter++;
        }

        return array_combine($xValues, $yValues);
    }

    public static function kdeNormalMedian(array $population, $bandwidth)
    {
        $kde = self::kdeNormal($population, $bandwidth);
        $keys = array_keys($kde, max($kde));

        return reset($keys);
    }

    public static function linspace($min, $max, $num = 50, $endpoint = true)
    {
        $range = $max - $min;
        $unit = $range / ($endpoint ? $num - 1 : $num);
        $space = array();

        for ($value = $min; $value <= $max; $value += $unit) {
            $space[] = $value;
        }

        return $space;
    }
}
