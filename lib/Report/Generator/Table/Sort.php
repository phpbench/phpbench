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

namespace PhpBench\Report\Generator\Table;

class Sort
{
    /**
     * Merge sort -- similar to usort but preserves order when comparing.
     *
     * http://at2.php.net/manual/en/function.usort.php#38827
     *
     * @param array $array
     * @param \Closure $callback Sorting callback
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
        self::mergeSort($array1, $callback);
        self::mergeSort($array2, $callback);

        // If all of $array1 is <= all of $array2, just append them.
        if (call_user_func($callback, end($array1), $array2[0]) < 1) {
            $array = array_merge($array1, $array2);

            return;
        }

        // Merge the two sorted arrays into a single sorted array
        $array = [];
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
    }
}
