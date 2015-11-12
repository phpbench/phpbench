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

use PhpBench\Benchmark\Remote\Payload;

/**
 * Represents the result of a single iteration executed by an executor.
 */
class IterationResult
{
    /**
     * @var Payload
     */
    private $payload;

    /**
     * @var int
     */
    private $time;

    /**
     * @var int
     */
    private $memory;

    /**
     * @param /Callable
     */
    public function __construct(Payload $payload)
    {
        $this->payload = $payload;
    }

    public function getTime()
    {
        $this->evaluate();
        return $this->time;
    }

    public function getMemory()
    {
        $this->evaluate();
        return $this->memory;
    }

    public function isRunning()
    {
        return $this->payload->isRunning();
    }

    public function wait()
    {
        $this->payload->wait();
    }

    private function evaluate()
    {
        if (null !== $this->time) {
            return;
        }

        $result = $this->payload->getResult();

        $this->time = $result['time'];
        $this->memory = $result['memory'];
    }
}
