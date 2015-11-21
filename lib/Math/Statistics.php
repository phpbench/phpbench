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
}
