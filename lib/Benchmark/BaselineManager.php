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
 * The baseline manager is responsible for collecting and executing
 * baseline benchmarks.
 *
 * Baseline benchmarks are standard microbenchmarks which can be used to
 * determine the "baseline" performance of the test platform.
 *
 * These measurements can be used to establish a baseline speed for the system,
 * or to provide counterweights to iteration measurements (and so attempt to
 * cancel out any fluctuations of the test platforms performance).
 */
class BaselineManager
{
    /**
     * @var mixed[]
     */
    private $callables;

    /**
     * Add a baseline callable. The callable can be any
     * callable accepted by call_user_func.
     *
     * Throws an invalid argument exception if the name has
     * already been registered.
     *
     * @param string $name
     * @param mixed $callable
     *
     * @throws \InvalidArgumentException
     */
    public function addBaselineCallable($name, $callable)
    {
        if (isset($this->callables[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Baseline callable "%s" has already been registered.',
                $name
            ));
        }

        if (!is_callable($callable)) {
            throw new \InvalidArgumentException(sprintf(
                'Given baseline "%s" callable "%s" is not callable.',
                $name, is_string($callable) ? $callable : gettype($callable)
            ));
        }

        $this->callables[$name] = $callable;
    }

    /**
     * Return mean time taken to execute the named baseline
     * callable in microseconds.
     *
     * @return float
     */
    public function benchmark($name, $revs)
    {
        if (!isset($this->callables[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown baseline callable "%s", known baseline callables: "%s"',
                $name, implode('", "', array_keys($this->callables))
            ));
        }

        $start = microtime(true);
        call_user_func($this->callables[$name], $revs);

        return (microtime(true) - $start) / $revs * 1E6;
    }
}
