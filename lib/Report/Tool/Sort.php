<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Tool;

class Sort
{
    /**
     * Sort a given key > value scalar array of arrays (rows) by reference.
     *
     * Sorting can either be an array of column names:
     *
     * ````
     * $sorting = array('col1', 'col2');
     * ````
     *
     * Where the default direction is ASC, or an associative array
     * where the key is the column name and the value is the direction:
     *
     * ````
     * $sorting = array('col1' => 'desc', 'col2' => 'asc');
     * ````
     *
     * @param array $array
     * @param array $sorting
     */
    public static function sortRows(array &$array, array $sorting)
    {
        foreach (array_reverse($sorting) as $column => $direction) {
            if (is_numeric($column)) {
                $column = $direction;
                $direction = 'asc';
            }

            self::mergesort($array, function ($row1, $row2) use ($column, $direction) {
                $row1Value = $row1[$column];
                $row2Value = $row2[$column];

                if ($row1Value == $row2Value) {
                    return 0;
                }

                $greaterThan = $row1Value > $row2Value;

                if (strtolower($direction) === 'asc') {
                    return $greaterThan ? 1 : -1;
                }

                return $greaterThan ? -1 : 1;
            });
        }
    }

    /**
     * Merge sort -- similar to usort but preserves order when comparison.
     *
     * http://at2.php.net/manual/en/function.usort.php#38827
     *
     * @param array
     * @param \Closure Sorting callback
     */
    public static function mergeSort(&$array, \Closure $callback)
    {
        // Arrays of size < 2 require no action.
        if (count($array) < 2) {
            return;
        }

        // Split the array in half
        $halfway = count($array) / 2;
        $array1 = array_slice($array, 0, $halfway);
        $array2 = array_slice($array, $halfway);

        // Recurse to sort the two halves
        self::mergesort($array1, $callback);
        self::mergesort($array2, $callback);

        // If all of $array1 is <= all of $array2, just append them.
        if (call_user_func($callback, end($array1), $array2[0]) < 1) {
            $array = array_merge($array1, $array2);

            return;
        }

        // Merge the two sorted arrays into a single sorted array
        $array = array();
        $ptr1 = $ptr2 = 0;
        while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
            if ($callback($array1[$ptr1], $array2[$ptr2]) < 1) {
                $array[] = $array1[$ptr1++];
            } else {
                $array[] = $array2[$ptr2++];
            }
        }
        // Merge the remainder
        while ($ptr1 < count($array1)) {
            $array[] = $array1[$ptr1++];
        }

        while ($ptr2 < count($array2)) {
            $array[] = $array2[$ptr2++];
        }

        return;
    }
}
