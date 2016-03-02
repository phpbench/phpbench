<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Model;

use PhpBench\Math\Distribution;
use PhpBench\Math\Statistics;

/**
 * Stores Iterations and calculates the deviations and rejection
 * status for each based on the given rejection threshold.
 *
 * TODO: Remove array access?
 */
class Variant implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * @var Subject
     */
    private $subject;

    /**
     * @var ParameterSet
     */
    private $parameterSet;

    /**
     * @var Iteration[]
     */
    private $iterations = [];

    /**
     * @var Iteration[]
     */
    private $rejects = [];

    /**
     * @var ErrorStack
     */
    private $errorStack;

    /**
     * @var Distribution
     */
    private $stats;

    /**
     * @var bool
     */
    private $computed = false;

    /**
     * @var int
     */
    private $revolutions;

    /**
     * @var int
     */
    private $warmup;

    public function __construct(
        Subject $subject,
        ParameterSet $parameterSet,
        $revolutions,
        $warmup
    ) {
        $this->subject = $subject;
        $this->parameterSet = $parameterSet;
        $this->revolutions = $revolutions;
        $this->warmup = $warmup;
    }

    /**
     * Generate $nbIterations and add them to the variant.
     *
     * @param int $nbIterations
     */
    public function spawnIterations($nbIterations)
    {
        for ($index = 0; $index < $nbIterations; $index++) {
            $this->iterations[] = new Iteration($index, $this);
        }
    }

    /**
     * Create and add a new iteration.
     *
     * @param int $time
     * @param int $memory
     *
     * @return Iteration
     */
    public function createIteration($time, $memory, $rejectionCount = 0)
    {
        $index = count($this->iterations);
        $iteration = $iteration = new Iteration($index, $this, $time, $memory, $rejectionCount);
        $this->iterations[] = $iteration;

        return $iteration;
    }

    /**
     * Return the iteration at the given index.
     *
     * @return Iteration
     */
    public function getIteration($index)
    {
        return $this->iterations[$index];
    }

    /**
     * Add an iteration.
     *
     * @param Iteration $iteration
     */
    public function addIteration(Iteration $iteration)
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
     * Return the iteration times.
     *
     * @return array
     */
    public function getTimes()
    {
        $times = [];
        foreach ($this->iterations as $iteration) {
            $times[] = $iteration->getRevTime();
        }

        return $times;
    }

    /**
     * Return the iteration memory measurements.
     *
     * @return array
     */
    public function getMemories()
    {
        $memories = [];
        foreach ($this->iterations as $iteration) {
            $memories[] = $iteration->getMemory();
        }

        return $memories;
    }

    /**
     * Return the Z-Values.
     *
     * @return float[]
     */
    public function getZValues()
    {
        $zValues = [];
        foreach ($this->iterations as $iteration) {
            $zValues[] = $iteration->getZValue();
        }

        return $zValues;
    }

    /**
     * Calculate and set the deviation from the mean time for each iteration. If
     * the deviation is greater than the rejection threshold, then mark the iteration as
     * rejected.
     */
    public function computeStats()
    {
        $this->rejects = [];
        $revs = $this->getRevolutions();

        if (0 === count($this->iterations)) {
            return;
        }

        $times = $this->getTimes();
        $retryThreshold = $this->getSubject()->getRetryThreshold();

        $this->stats = new Distribution($times);

        foreach ($this->iterations as $iteration) {
            // deviation is the percentage different of the value from the mean of the set.
            if ($this->stats->getMean() > 0) {
                $deviation = 100 / $this->stats->getMean() * (
                    (
                        $iteration->getRevTime()
                    ) - $this->stats->getMean()
                );
            } else {
                $deviation = 0;
            }
            $iteration->setDeviation($deviation);

            // the Z-Value represents the number of standard deviations this
            // value is away from the mean.
            $revTime = $iteration->getTime() / $revs;
            $zValue = $this->stats->getStdev() ? ($revTime - $this->stats->getMean()) / $this->stats->getStdev() : 0;
            $iteration->setZValue($zValue);

            if (null !== $retryThreshold) {
                if (abs($deviation) >= $retryThreshold) {
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
        if (null !== $this->errorStack) {
            throw new \RuntimeException(sprintf(
                'Cannot retrieve stats when an exception was encountered ([%s] %s)',
                $this->errorStack->getTop()->getClass(),
                $this->errorStack->getTop()->getMessage()
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
     * Return true if the collection has been computed (i.e. stats have been s
     * set and rejects identified).
     *
     * @return bool
     */
    public function isComputed()
    {
        return $this->computed;
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
     * @return Subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Return true if any of the iterations in this set encountered
     * an error.
     *
     * @return bool
     */
    public function hasErrorStack()
    {
        return null !== $this->errorStack;
    }

    /**
     * Should be called when rebuiling the object graph.
     *
     * @return ErrorStack
     */
    public function getErrorStack()
    {
        if (null === $this->errorStack) {
            return new ErrorStack($this, []);
        }

        return $this->errorStack;
    }

    /**
     * Should be called when an Exception is encountered during
     * the execution of any of the iteration processes.
     *
     * After an exception is encountered the results from this iteration
     * set are invalid.
     *
     * @param \Exception $e
     */
    public function setException(\Exception $exception)
    {
        $errors = [];

        do {
            $errors[] = Error::fromException($exception);
        } while ($exception = $exception->getPrevious());

        $this->errorStack = new ErrorStack($this, $errors);
    }

    public function createErrorStack(array $errors)
    {
        $this->errorStack = new ErrorStack($this, $errors);
    }

    /**
     * Return the number of revolutions for this variant.
     *
     * @return int
     */
    public function getRevolutions()
    {
        return $this->revolutions;
    }

    /**
     * Return the number of warmup revolutions.
     *
     * @return int
     */
    public function getWarmup()
    {
        return $this->warmup;
    }

    /**
     * Return all the iterations.
     *
     * @return Iteration[]
     */
    public function getIterations()
    {
        return $this->iterations;
    }

    /**
     * Return number of iterations.
     *
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->iterations);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getIteration($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        throw new \InvalidArgumentException(
            'Iteration collections are immutable'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new \InvalidArgumentException(
            'Iteration collections are immutable'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->iterations);
    }
}
