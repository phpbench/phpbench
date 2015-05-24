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

class SuiteResult
{
    private $benchmarkResults;

    public function __construct(array $benchmarkResults)
    {
        $this->benchmarkResults = $benchmarkResults;
    }

    public function getBenchmarkResults()
    {
        return $this->benchmarkResults;
    }

    public function getIterationsResults()
    {
        $iterationsResults = array();
        foreach ($this->getSubjectResults() as $subjectResult) {
            foreach ($subjectResult->getIterationsResults() as $iterationResult) {
                $iterationsResults[] = $iterationResult;
            }
        }

        return $iterationsResults;
    }

    public function getSubjectResults()
    {
        $subjectResults = array();
        foreach ($this->benchmarkResults as $benchmarkResult) {
            foreach ($benchmarkResult->getSubjectResults() as $subjectResult) {
                $subjectResults[] = $subjectResult;
            }
        }

        return $subjectResults;
    }
}
