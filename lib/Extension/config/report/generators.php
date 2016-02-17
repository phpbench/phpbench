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
        'cols' => array('benchmark', 'subject', 'groups', 'params', 'revs', 'its', 'mem', 'best', 'mean', 'mode', 'worst', 'stdev', 'rstdev', 'diff'),
    ),
    'default' => array(
        'generator' => 'table',
        'iterations' => true,
        'cols' => array('benchmark', 'subject', 'groups', 'params', 'revs', 'iter', 'rej', 'mem', 'iter', 'time', 'z-value', 'diff'),
        'diff_col' => 'time',
    ),
    'compare' => array(
        'generator' => 'table',
        'cols' => array('benchmark', 'subject', 'groups', 'params', 'revs'),
        'compare' => 'vcs_branch',
        'break' => array('benchmark', 'subject'),
    ),
    'env' => array(
        'generator' => 'env',
    ),
);
