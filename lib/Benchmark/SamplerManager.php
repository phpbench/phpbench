<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Benchmark;

/**
 * The sampler manager is responsible for collecting and executing
 * sampler benchmarks.
 *
 * Baseline benchmarks are standard microbenchmarks which can be used to
 * determine the "sampler" performance of the test platform.
 *
 * These measurements can be used to establish a sampler speed for the system,
 * or to provide counterweights to iteration measurements (and so attempt to
 * cancel out any fluctuations of the test platforms performance).
 */
class SamplerManager
{
    /**
     * @var mixed[]
     */
    private $callables;

    /**
     * Add a sampler callable. The callable can be any
     * callable accepted by call_user_func.
     *
     * Throws an invalid argument exception if the name has
     * already been registered.
     *
     *
     * @throws \InvalidArgumentException
     */
    public function addSamplerCallable(string $name, $callable): void
    {
        if (isset($this->callables[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Baseline callable "%s" has already been registered.',
                $name
            ));
        }

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf(
                'Given sampler "%s" callable "%s" is not callable.',
                $name,
                is_string($callable) ? $callable : gettype($callable)
            ));
        }

        $this->callables[$name] = $callable;
    }

    /**
     * Return mean time taken to execute the named sampler
     * callable in microseconds.
     */
    public function sample($name, $revs): float
    {
        if (!isset($this->callables[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown sampler callable "%s", known baseline callables: "%s"',
                $name,
                implode('", "', array_keys($this->callables))
            ));
        }

        $start = microtime(true);
        call_user_func($this->callables[$name], $revs);

        return (microtime(true) - $start) / $revs * 1E6;
    }
}
