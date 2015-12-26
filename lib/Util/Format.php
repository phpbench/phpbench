<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
