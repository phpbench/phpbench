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

namespace PhpBench\Model;

use PhpBench\Assertion\AssertionFailure;
use PhpBench\Assertion\AssertionFailures;
use PhpBench\Assertion\AssertionWarning;
use PhpBench\Assertion\AssertionWarnings;
use PhpBench\Math\Distribution;
use PhpBench\Math\Statistics;
use PhpBench\Model\Result\ComputedResult;
use PhpBench\Model\Result\TimeResult;

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
     * @var array
     */
    private $computedStats;

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

    /**
     * @var AssertionFailures
     */
    private $failures;

    /**
     * @var AssertionWarnings
     */
    private $warnings;

    public function __construct(
        Subject $subject,
        ParameterSet $parameterSet,
        $revolutions,
        $warmup,
        array $computedStats = []
    ) {
        $this->subject = $subject;
        $this->parameterSet = $parameterSet;
        $this->revolutions = $revolutions;
        $this->warmup = $warmup;
        $this->computedStats = $computedStats;
        $this->failures = new AssertionFailures($this);
        $this->warnings = new AssertionWarnings($this);
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
    public function createIteration(array $results = [])
    {
        $index = count($this->iterations);
        $iteration = new Iteration($index, $this, $results);
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
     * Return result values by class and metric name.
     *
     * e.g.
     *
     * ```
     * $variant->getMetricValues(ComputedResult::class, 'z_value');
     * ```
     *
     * @return mixed[]
     */
    public function getMetricValues($resultClass, $metricName)
    {
        $values = [];
        foreach ($this->iterations as $iteration) {
            if ($iteration->hasResult($resultClass)) {
                $values[] = $iteration->getMetric($resultClass, $metricName);
            }
        }

        return $values;
    }

    /**
     * Return the average metric values by revolution.
     *
     * @return mixed[]
     */
    public function getMetricValuesByRev($resultClass, $metric)
    {
        return array_map(function ($value) {
            return $value / $this->getRevolutions();
        }, $this->getMetricValues($resultClass, $metric));
    }

    public function resetAssertionResults()
    {
        $this->warnings = new AssertionWarnings($this);
        $this->failures = new AssertionFailures($this);
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

        $times = $this->getMetricValuesByRev(TimeResult::class, 'net');
        $retryThreshold = $this->getSubject()->getRetryThreshold();

        $this->stats = new Distribution($times, $this->computedStats);

        foreach ($this->iterations as $iteration) {
            // deviation is the percentage different of the value from the mean of the set.
            if ($this->stats->getMean() > 0) {
                $deviation = 100 / $this->stats->getMean() * (
                    (
                        $iteration->getResult(TimeResult::class)->getRevTime($iteration->getVariant()->getRevolutions())
                    ) - $this->stats->getMean()
                );
            } else {
                $deviation = 0;
            }

            // the Z-Value represents the number of standard deviations this
            // value is away from the mean.
            $revTime = $iteration->getResult(TimeResult::class)->getRevTime($revs);
            $zValue = $this->stats->getStdev() ? ($revTime - $this->stats->getMean()) / $this->stats->getStdev() : 0;

            if (null !== $retryThreshold) {
                if (abs($deviation) >= $retryThreshold) {
                    $this->rejects[] = $iteration;
                }
            }

            $iteration->setResult(new ComputedResult($zValue, $deviation));
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
     * TODO: Rename to getDistribution
     *
     * @return Distribution
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
     * Create an error stack from an Exception.
     *
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

    public function addFailure(AssertionFailure $failure)
    {
        $this->failures->add($failure);
    }

    public function addWarning(AssertionWarning $warning)
    {
        $this->warnings->add($warning);
    }

    public function hasFailed()
    {
        return count($this->failures) > 0;
    }

    public function hasWarning()
    {
        return count($this->warnings) > 0;
    }

    public function getFailures(): AssertionFailures
    {
        return $this->failures;
    }

    /**
     * Create and set the error stack from a list of Error instances.
     *
     * @param Error[]
     */
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

    public function getWarnings(): AssertionWarnings
    {
        return $this->warnings;
    }
}
