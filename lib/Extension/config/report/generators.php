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
    'aggregate' => [
        'generator' => 'expression',
    ],
    'default' => [
        'generator' => 'expression',
        'cols' => [
            'benchmark' => 'first(subject_name)',
            'subject' => 'first(subject_name)',
            'set' => 'first(variant_name)',
            'revs' => 'first(variant_revs)',
            'mem_peak' => 'first(result_mem_peak) as bytes',
            'time_avg' => 'first(result_time_avg) as time',
        ],
        'aggregate' => ['benchmark_class', 'subject_name', 'variant_name', 'iteration_index'],
    ],
    'memory' => [
        'generator' => 'expression',
        'iterations' => true,
        'cols' => ['benchmark', 'subject', 'set', 'revs', 'iter', 'mem_final', 'mem_real', 'mem_peak'],
    ],
    'compare' => [
        'generator' => 'expression',
        'cols' => ['benchmark', 'subject', 'set', 'revs'],
        'compare' => 'suite',
        'break' => [],
    ]
];
