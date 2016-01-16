<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return array(
    'microtime' => array(
        'executor' => 'microtime',
    ),
    'debug' => array(
        'executor' => 'debug',
    ),
    'debug_macro' => array(
        'executor' => 'debug',
        'times' => array(1000000, 200000),
        'spread' => array(50000, -12345, 1000),
    ),
);
