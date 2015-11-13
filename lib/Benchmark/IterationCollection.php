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
 * Stores Iterations and calculates the deviations and rejection
 * status for each based on the given rejection threshold.
 */
class IterationCollection implements \IteratorAggregate
{
    /**
     * @var Iteration[]
     */
    private $iterations = array();

    /**
     * @var Iteration[]
     */
    private $rejects = array();

    /**
     * @var float
     */
    private $rejectionThreshold;

    /**
     * @var int
     */
    private $concurrency;

    /**
     * @param float $rejectionThreshold
     */
    public function __construct($rejectionThreshold = null, $concurrency = 1)
    {
        $this->rejectionThreshold = $rejectionThreshold;
        $this->concurrency = $concurrency;
    }

    /**
     * Replace the iterations in the collection with the given iterations.
     *
     * @param Iteration[] $iterations
     */
    public function replace(array $iterations)
    {
        $this->iterations = $iterations;
    }

    /**
     * Add an iteration.
     *
     * @param Iteration $iteration
     */
    public function add(Iteration $iteration)
    {
        $this->iterations[] = $iteration;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->iterations);
    }

    /**
     * Calculate and set the deviation from the mean time for each iteration. If
     * the deviation is greater than the rejection threshold, then mark the iteration as
     * rejected.
     */
    public function computeDeviations()
    {
        $this->rejects = array();
        $average = $total = null;

        if (0 === count($this->iterations)) {
            return;
        }

        foreach ($this->iterations as $iteration) {
            $total += $iteration->getResult()->getTime();
        }
        $average = $total / count($this->iterations);

        foreach ($this->iterations as $iteration) {
            // deviation is the percentage different of the value from the average of the set. We
            // use abs() to always obtain a positive number.
            $deviation = abs(100 / $average * ($iteration->getResult()->getTime() - $average));
            $iteration->setDeviation($deviation);

            if (null !== $this->rejectionThreshold) {
                if ($deviation >= $this->rejectionThreshold) {
                    $this->rejects[] = $iteration;
                }
            }
        }
    }

    /**
     * Wait, if the number of running processes equal to or more than the allowed concurrency.
     */
    public function wait()
    {
        while ($this->getRunningCount() >= $this->concurrency) {
            foreach ($this->iterations as $iteration) {
                // this is not ideal as it means that we always wait for the end of a specific,
                // arbitrary, iteration before we can start a new one if we fall below the concurrency
                // limit.
                $iteration->getResult()->wait();
                // todo: use callbacks?
            }
        }
    }

    /**
     * Return the number of iterations that are currently running.
     *
     * @return int
     */
    private function getRunningCount()
    {
        $nbRunning = 0;

        foreach ($this->iterations as $iteration) {
            $nbRunning += $iteration->getResult()->isReady() ? 0 : 1;
        }

        return $nbRunning;
    }

    /**
     * Return the number of rejected iterations.
     *
     * @return int
     */
    public function getRejectCount()
    {
        return count($this->rejects);
    }

    /**
     * Return all rejected iterations.
     *
     * @return Iteration[]
     */
    public function getRejects()
    {
        return $this->rejects;
    }
}
