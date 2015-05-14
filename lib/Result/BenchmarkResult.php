<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Result;

class BenchmarkResult
{
    private $subjectResults;
    private $class;

    public function __construct($class, array $subjectResults)
    {
        $this->subjectResults = $subjectResults;
        $this->class = $class;
    }

    public function getSubjectResults()
    {
        return $this->subjectResults;
    }

    public function getClass()
    {
        return $this->class;
    }
}
