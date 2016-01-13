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
        'title' => 'Aggregate report',
        'generator' => 'table',
        'type' => 'aggregate',
    ),
    'default' => array(
        'title' => 'Iteration report',
        'generator' => 'table',
        'type' => 'default',
    ),
    'compare' => array(
        'title' => 'Suite comparison',
        'generator' => 'table',
        'type' => 'compare',
    ),
    'plain' => array(
        'generator' => 'table',
        'type' => 'default',
        'formatting' => false,
        'body_only' => true,
    ),
    'env' => array(
        'generator' => 'table_custom',
        'file' => __DIR__ . '/../../../Report/Generator/Tabular/env.json',
        'title' => 'Environment',
    ),
);
