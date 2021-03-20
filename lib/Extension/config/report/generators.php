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
            'iter' => 'first(iteration_index)',
            'benchmark' => 'first(subject_name)',
            'subject' => 'first(subject_name)',
            'set' => 'first(variant_name)',
            'revs' => 'first(variant_revs)',
            'mem_peak' => 'first(result_mem_peak) as bytes',
            'time_avg' => 'first(result_time_avg) as time',
            'comp_z_value' => 'first(result_comp_z_value)',
            'comp_deviation' => 'first(result_comp_deviation)',
        ],
        'aggregate' => ['benchmark_class', 'subject_name', 'variant_name', 'iteration_index'],
    ],
    'memory' => [
        'generator' => 'expression',
        'aggregate' => ['benchmark_class', 'subject_name', 'variant_name', 'iteration_index'],
        'cols' => [
            'iter' => 'first(iteration_index)',
            'benchmark' => 'first(subject_name)',
            'subject' => 'first(subject_name)',
            'set' => 'first(variant_name)',
            'revs' => 'first(variant_revs)',
            'mem_final' => 'first(result_mem_final) as memory',
            'mem_real' => 'first(result_mem_real) as memory',
            'mem_peak' => 'first(result_mem_peak) as memory',
        ],
    ]
];
