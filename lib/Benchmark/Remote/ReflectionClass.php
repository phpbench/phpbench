<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark\Remote;

class ReflectionClass
{
    public $path;
    public $interfaces = array();
    public $class;
    public $abstract = false;
    public $comment;
    public $methods = array();
}
