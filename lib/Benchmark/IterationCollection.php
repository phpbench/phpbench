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

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Math\Statistics;

/**
 * Stores Iterations and calculates the deviations and rejection
 * status for each based on the given rejection threshold.
 */
class IterationCollection implements \IteratorAggregate
{
    /**
     * @var SubjectMetadata
     */
    private $subject;

    /**
     * @var ParameterSet
     */
    private $parameterSet;

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
     * @var \Exception
     */
    private $exception;

    /**
     * Array of statistics:.
     *
     *   mean      float Mean sample time
     *   stdev     float The standard deviation
     *   rstdev    float Relative standard deviation
     *   variance  float Variance
     *   min       float Minimum sample value
     *   max       float Maximum sample value
     *
     * @var array
     */
    private $stats = array(
        'mean' => null,
        'stdev' => null,
        'rstdev' => null,
        'variance' => null,
        'min' => null,
        'max' => null,
    );

    /**
     * @var boolean
     */
    private $computed = false;

    /**
     * @param float $rejectionThreshold
     */
    public function __construct(SubjectMetadata $subject, ParameterSet $parameterSet, $rejectionThreshold = null)
    {
        $this->subject = $subject;
        $this->parameterSet = $parameterSet;
        $this->rejectionThreshold = $rejectionThreshold;
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
    public function computeStats()
    {
        $this->rejects = array();

        if (0 === count($this->iterations)) {
            return;
        }

        $times = array();
        foreach ($this->iterations as $iteration) {
            $times[] = $iteration->getResult()->getTime() / $iteration->getRevolutions();
        }

        // standard deviation for T Distribution
        $this->stats['stdev'] = Statistics::stdev($times);

        // mean of the times
        $this->stats['mean'] = Statistics::mean($times);

        // standard error
        $this->stats['rstdev'] = $this->stats['stdev'] / $this->stats['mean'] * 100;

        // variance
        $this->stats['variance'] = Statistics::variance($times);

        // min and max
        $this->stats['min'] = min($times);
        $this->stats['max'] = max($times);

        foreach ($this->iterations as $iteration) {
            // deviation is the percentage different of the value from the mean of the set.
            $deviation = 100 / $this->stats['mean'] * (($iteration->getResult()->getTime() / $iteration->getRevolutions()) - $this->stats['mean']);
            $iteration->setDeviation($deviation);

            // the Z-Value repreents the number of standard deviations this
            // value is away from the mean.
            $revTime = $iteration->getResult()->getTime() / $iteration->getRevolutions();
            $zValue = $this->stats['stdev'] ? ($revTime - $this->stats['mean']) / $this->stats['stdev'] : 0;
            $iteration->setZValue($zValue);

            if (null !== $this->rejectionThreshold) {
                if (abs($deviation) >= $this->rejectionThreshold) {
                    $this->rejects[] = $iteration;
                }
            }
        }

        $this->computed = true;
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

    /**
     * Return statistics about this iteration collection.
     *
     * See self::$stats.
     *
     * @return array
     */
    public function getStats()
    {
        if (null !== $this->exception) {
            throw new \RuntimeException(sprintf(
                'Cannot retrieve stats when an exception was encountered ([%s] %s)',
                get_class($this->exception),
                $this->exception->getMessage()
            ));
        }

        if (false === $this->computed) {
            throw new \RuntimeException(
                'No statistics have yet been computed for this iteration set (::computeStats should be called)'
            );
        }

        return $this->stats;
    }

    /**
     * Spawn a given number of iterations with a given number of revolutions each.
     *
     * @param int $count
     * @param int $revolutionCount
     *
     * @return Iteration[]
     */
    public function spawnIterations($count, $revolutionCount)
    {
        $iterations = array();

        for ($index = 0; $index < $count; $index++) {
            $iterations[] = new Iteration($index, $this->subject, $revolutionCount, $this->parameterSet);
        }

        return $iterations;
    }

    /**
     * Return the parameter set.
     *
     * @return ParameterSet
     */
    public function getParameterSet()
    {
        return $this->parameterSet;
    }

    /**
     * Return the subject metadata.
     *
     * @return SubjectMetadata
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Return true if any of the iterations in this set encountered
     * an error.
     *
     * @return boolean
     */
    public function hasException()
    {
        return null !== $this->exception;
    }

    /**
     * Should be called when an Exception is encountered during
     * the excecution of any of the iteration processes.
     *
     * After an exception is encountered the results from this iteration
     * set are invalid.
     *
     * @param \Exception $e
     */
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Return an exception if it has been set.
     *
     * @throws \RuntimeException if no exception is set.
     * @return \Exception
     */
    public function getException()
    {
        if (null === $this->exception) {
            throw new \RuntimeException(
                'No exception has been set on this iteration collection'
            );
        }

        return $this->exception;
    }
}
