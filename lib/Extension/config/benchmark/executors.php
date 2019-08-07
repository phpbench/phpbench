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

return [
    'memory_centric_microtime' => [
        'executor' => 'memory_centric_microtime',
    ],
    'microtime' => [
        'executor' => 'microtime',
    ],
    'debug' => [
        'executor' => 'debug',
    ],
    'debug_macro' => [
        'executor' => 'debug',
        'times' => [1000000, 200000],
        'spread' => [50000, -12345, 1000],
    ],
];
