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

class BenchIteration
{
    private $index;
    private $parameters;
    private $time;
    private $memory;
    private $memoryDiff;
    private $memoryInclusive;
    private $memoryDiffInclusive;

    public function __construct($index, $parameters)
    {
        $this->index = $index;
        $this->parameters = $parameters;
    }

    public function getParameter($name)
    {
        if (!isset($this->parameters[$name])) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unknown iteration parameters "%s"', $name
            ));
        }

        return $this->parameters[$name];
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    public function getMemory() 
    {
        return $this->memory;
    }
    
    public function setMemory($memory)
    {
        $this->memory = $memory;
    }

    public function getMemoryDiff() 
    {
        return $this->memoryDiff;
    }
    
    public function setMemoryDiff($memoryDiff)
    {
        $this->memoryDiff = $memoryDiff;
    }

    public function getMemoryInclusive() 
    {
        return $this->memoryInclusive;
    }
    
    public function setMemoryInclusive($memoryInclusive)
    {
        $this->memoryInclusive = $memoryInclusive;
    }

    public function getMemoryDiffInclusive() 
    {
        return $this->memoryDiffInclusive;
    }
    
    public function setMemoryDiffInclusive($memoryDiffInclusive)
    {
        $this->memoryDiffInclusive = $memoryDiffInclusive;
    }
}
