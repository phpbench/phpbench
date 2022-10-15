<?php

namespace PhpBench\Util;

use function number_format;

class NumberFormat
{
    /**
     * Similar to the built-in number_format but meaningless zeros after the
     * decimal place are trimmed.
     */
    public static function format(float $number, int $decimals, bool $stripTailingZeros): string
    {
        $formated = number_format($number, $decimals);

        if ($stripTailingZeros && false !== strpos($formated, '.')) {
            $formated = rtrim($formated, '0');

            if (substr($formated, -1) === '.') {
                return (string)number_format($number);
            }
        }

        return $formated;
    }
}
