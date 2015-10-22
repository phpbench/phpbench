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

/**
 * Represents the result of a single iteration executed by an executor.
 */
class IterationResult
{
    /**
     * @var int
     */
    private $time;

    /**
     * @var int
     */
    private $memory;

    /**
     * @param mixed $time Time taken to execute the iteration in microseconds.
     * @param mixed $memory Memory used by iteration in bytes.
     */
    public function __construct($time, $memory)
    {
        $this->time = $time;
        $this->memory = $memory;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getMemory()
    {
        return $this->memory;
    }
}
