<?php

namespace PhpBench\Util;

class Format
{
    public static function truncate($string, $length)
    {
        if (strlen($string) > $length) {
            return substr($string, 0, $length - 3) . '...';
        }

        return $string;
    }
}
