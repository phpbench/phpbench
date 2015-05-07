<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

class BenchAggregateIterationResult
{
    private $iterations;
    private $parameters;

    public function __construct($iterations, $parameters)
    {
        $this->iterations = $iterations;
        $this->parameters = $parameters;
    }

    public function getIterations()
    {
        return $this->iterations;
    }

    public function getIterationCount()
    {
        return count($this->iterations);
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getTimes()
    {
        $times = array();
        foreach ($this->iterations as $iteration) {
            $times[] = $iteration->getTime();
        }

        return $times;
    }

    public function getTotalTime()
    {
        $totalTime = 0;
        foreach ($this->getTimes() as $time) {
            $totalTime += $time;
        }

        return $totalTime;
    }

    public function getAverageTime()
    {
        return ($this->getTotalTime() / count($this->iterations));
    }

    public function getMinTime()
    {
        return min($this->getTimes());
    }

    public function getMaxTime()
    {
        return max($this->getTimes());
    }
}
