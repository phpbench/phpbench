<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'aggregate' => [
        'generator' => 'table',
        'cols' => ['benchmark', 'subject', 'params', 'revs', 'its', 'mem', 'best', 'mean', 'mode', 'worst', 'stdev', 'rstdev', 'diff'],
    ],
    'default' => [
        'generator' => 'table',
        'iterations' => true,
        'cols' => ['benchmark', 'subject', 'params', 'revs', 'iter', 'rej', 'mem', 'iter', 'time', 'z-value', 'diff'],
        'diff_col' => 'time',
    ],
    'compare' => [
        'generator' => 'table',
        'cols' => ['benchmark', 'subject', 'params', 'revs'],
        'compare' => 'vcs_branch',
        'break' => ['benchmark', 'subject'],
    ],
    'env' => [
        'generator' => 'env',
    ],
];
