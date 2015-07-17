<?php

/*
 * This file is part of the Table Data package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report;

/**
 * Calculator.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class Calculator
{
    /**
     * Return the sum of all the given values.
     *
     * @param array $values
     *
     * @return mixed
     */
    public static function sum($values)
    {
        $sum = 0;
        foreach (self::getValues($values) as $value) {
            $sum += $value;
        }

        return $sum;
    }

    /**
     * Return the lowest value contained within the given values.
     *
     * @param array $values
     *
     * @return mixed
     */
    public static function min($values)
    {
        $min = null;
        foreach (self::getValues($values) as $value) {
            if (null === $min || $value < $min) {
                $min = $value;
            }
        }

        return $min;
    }

    /**
     * Return the highest value contained within the given values.
     *
     * @param array $values
     *
     * @return mixed
     */
    public static function max($values)
    {
        $max = null;
        foreach (self::getValues($values) as $value) {
            if (null === $max || $value > $max) {
                $max = $value;
            }
        }

        return $max;
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

        $values = self::getValues($values);

        $sum = self::sum($values);

        if (0 == $sum) {
            return 0;
        }

        $count = count($values);

        return $sum / $count;
    }

    /**
     * Return the median value of the given values.
     *
     * @param array $values
     *
     * @return mixed
     */
    public static function median($values)
    {
        if (empty($values)) {
            return 0;
        }

        $values = self::getValues($values);

        sort($values);
        $nbValues = count($values);
        $middleIndex = $nbValues / 2;

        if (count($values) % 2 == 1) {
            return $values[ceil($middleIndex) - 1];
        }

        return ($values[$middleIndex - 1] + $values[$middleIndex]) / 2;
    }

    /**
     * Return the deviation as a percentage from the given value.
     *
     * @param mixed $standardValue
     * @param mixed $actualValue
     *
     * @return int
     */
    public static function deviation($standardValue, $actualValue)
    {
        $actualValue = self::getValue($actualValue);

        if (0 == $standardValue) {
            return $actualValue;
        }

        if (!is_numeric($standardValue) || !is_numeric($actualValue)) {
            throw new \RuntimeException(
                'Deviation must be passed numeric values.'
            );
        }

        return 100 / $standardValue * ($actualValue - $standardValue);
    }

    private static function getValues($values)
    {
        foreach ($values as &$value) {
            if ($value instanceof \DOMNode) {
                $value = $value->nodeValue;
            }

            if (!is_scalar($value)) {
                throw new \InvalidArgumentException(sprintf('Values passed as an array must be scalar, got "%s"',
                    is_object($value) ? get_class($value) : gettype($value)
                ));
            }
        }

        return $values;
    }

    private static function getValue($value)
    {
        $values = self::getValues(array($value));

        return reset($values);
    }
}
