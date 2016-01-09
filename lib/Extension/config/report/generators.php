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
    'aggregate' => array(
        'generator' => 'table',
        'type' => 'aggregate',
    ),
    'default' => array(
        'generator' => 'table',
        'type' => 'default',
    ),
    'compare' => array(
        'generator' => 'table',
        'type' => 'compare',
    ),
    'plain' => array(
        'generator' => 'table',
        'type' => 'default',
        'formatting' => false,
        'body_only' => true,
    ),
    'histogram' => array(
        'generator' => 'histogram',
    ),
);
