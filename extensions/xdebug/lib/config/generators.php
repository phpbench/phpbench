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
    'xdebug_trace' => [
        'generator' => 'table',
        'iterations' => true,
        'cols' => ['benchmark', 'subject', 'xdebug_memory', 'xdebug_time', 'xdebug_nb_calls'],
        'class_map' => [
            'xdebug_time' => ['timeunit'],
            'xdebug_memory' => ['mem'],
            'xdebug_nb_calls' => ['integer'],
        ],
    ],
];
