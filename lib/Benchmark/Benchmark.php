<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

class Benchmark
{
    private $path;
    private $classFqn;
    private $subjects = array();

    public function __construct($path, $classFqn)
    {
        $this->path = $path;
        $this->classFqn = $classFqn;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getClassFqn()
    {
        return $this->classFqn;
    }

    public function getSubjects()
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject)
    {
        $this->subjects[] = $subject;
    }
}
