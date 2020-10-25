<?php

namespace PhpBench\Functional;

class Functional
{
    public static function group(iterable $collection, callable $callback): iterable
    {
        $groups = [];

        foreach ($collection as $index => $element) {
            $groupKey = $callback($element, $index, $collection);

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [];
            }

            $groups[$groupKey][$index] = $element;
        }

        return $groups;
    }

    public static function map(iterable $collection, callable $callback): iterable
    {
        $aggregation = [];

        foreach ($collection as $index => $element) {
            $aggregation[$index] = $callback($element, $index, $collection);
        }

        return $aggregation;
    }

    /**
     * @return mixed
     */
    public static function reduceLeft(iterable $collection, callable $callback, $initial = null)
    {
        foreach ($collection as $index => $value) {
            $initial = $callback($value, $index, $collection, $initial);
        }

        return $initial;
    }
}
