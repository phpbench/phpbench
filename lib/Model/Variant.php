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

use ArrayAccess;
use ArrayIterator;
use Countable;
use Exception;
use IteratorAggregate;
use PhpBench\Assertion\AssertionFailure;
use PhpBench\Assertion\AssertionFailures;
use PhpBench\Assertion\AssertionWarning;
use PhpBench\Assertion\AssertionWarnings;
use PhpBench\Math\Distribution;
use PhpBench\Math\Statistics;
use PhpBench\Model\Result\ComputedResult;
use PhpBench\Model\Result\TimeResult;
use RuntimeException;

/**
 * Stores Iterations and calculates the deviations and rejection
 * status for each based on the given rejection threshold.
 *
 * @implements IteratorAggregate<Iteration>
 * @implements ArrayAccess<string, Iteration>
 */
class Variant implements IteratorAggregate, ArrayAccess, Countable
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

    /**
     * @var Variant|null
     */
    private $baseline;

    public function __construct(
        Subject $subject,
        ParameterSet $parameterSet,
        int $revolutions,
        int $warmup,
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
    public function spawnIterations($nbIterations): void
    {
        for ($index = 0; $index < $nbIterations; $index++) {
            $this->iterations[] = new Iteration($index, $this);
        }
    }

    /**
     * Create and add a new iteration.
     *
     * @param array<ResultInterface> $results
     */
    public function createIteration(array $results = []): Iteration
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
    public function getIteration($index): ?Iteration
    {
        return $this->iterations[$index] ?? null;
    }

    /**
     * Add an iteration.
     *
     * @param Iteration $iteration
     */
    public function addIteration(Iteration $iteration): void
    {
        $this->iterations[] = $iteration;
    }

    /**
     * @return ArrayIterator<int,Iteration>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->iterations);
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
    public function getMetricValues(string $resultClass, string $metricName): array
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
    public function getMetricValuesByRev(string $resultClass, string $metric): array
    {
        return array_map(function ($value) {
            return $value / $this->getRevolutions();
        }, $this->getMetricValues($resultClass, $metric));
    }

    public function resetAssertionResults(): void
    {
        $this->warnings = new AssertionWarnings($this);
        $this->failures = new AssertionFailures($this);
    }

    /**
     * Calculate and set the deviation from the mean time for each iteration. If
     * the deviation is greater than the rejection threshold, then mark the iteration as
     * rejected.
     */
    public function computeStats(): void
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
            $timeResult = $iteration->getResult(TimeResult::class);
            assert($timeResult instanceof TimeResult);
            // deviation is the percentage different of the value from the mean of the set.
            if ($this->stats->getMean() > 0) {
                $deviation = 100 / $this->stats->getMean() * (
                    (
                        $timeResult->getRevTime($iteration->getVariant()->getRevolutions())
                    ) - $this->stats->getMean()
                );
            } else {
                $deviation = 0;
            }

            // the Z-Value represents the number of standard deviations this
            // value is away from the mean.
            $revTime = $timeResult->getRevTime($revs);
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
    public function getRejectCount(): int
    {
        return count($this->rejects);
    }

    /**
     * Return all rejected iterations.
     *
     * @return Iteration[]
     */
    public function getRejects(): array
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
    public function getStats(): Distribution
    {
        if (null !== $this->errorStack) {
            throw new RuntimeException(sprintf(
                'Cannot retrieve stats when an exception was encountered ([%s] %s)',
                $this->errorStack->getTop()->getClass(),
                $this->errorStack->getTop()->getMessage()
            ));
        }

        if (false === $this->computed) {
            throw new RuntimeException(
                'No statistics have yet been computed for this iteration set (::computeStats should be called)'
            );
        }

        return $this->stats;
    }

    /**
     * Return true if the collection has been computed (i.e. stats have been s
     * set and rejects identified).
     */
    public function isComputed(): bool
    {
        return $this->computed;
    }

    /**
     * Return the parameter set.
     */
    public function getParameterSet(): ParameterSet
    {
        return $this->parameterSet;
    }

    /**
     * Return the subject metadata.
     */
    public function getSubject(): Subject
    {
        return $this->subject;
    }

    /**
     * Return true if any of the iterations in this set encountered
     * an error.
     */
    public function hasErrorStack(): bool
    {
        return null !== $this->errorStack;
    }

    /**
     * Should be called when rebuiling the object graph.
     */
    public function getErrorStack(): ErrorStack
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
     * @param \Exception $exception
     */
    public function setException(\Exception $exception): void
    {
        $errors = [];

        do {
            $errors[] = Error::fromException($exception);
        } while ($exception = $exception->getPrevious());

        $this->errorStack = new ErrorStack($this, $errors);
    }

    public function addFailure(AssertionFailure $failure): void
    {
        $this->failures->add($failure);
    }

    public function addWarning(AssertionWarning $warning): void
    {
        $this->warnings->add($warning);
    }

    public function hasFailed(): bool
    {
        return count($this->failures) > 0;
    }

    public function hasWarning(): bool
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
     * @param Error[] $errors
     */
    public function createErrorStack(array $errors): void
    {
        $this->errorStack = new ErrorStack($this, $errors);
    }

    /**
     * Return the number of revolutions for this variant.
     */
    public function getRevolutions(): int
    {
        return $this->revolutions;
    }

    /**
     * Return the number of warmup revolutions.
     */
    public function getWarmup(): int
    {
        return $this->warmup;
    }

    /**
     * Return all the iterations.
     *
     * @return Iteration[]
     */
    public function getIterations(): array
    {
        return $this->iterations;
    }

    /**
     * Return number of iterations.
     */
    public function count(): int
    {
        return count($this->iterations);
    }

    public function offsetGet($offset)
    {
        return $this->getIteration($offset);
    }

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

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->iterations);
    }

    public function getWarnings(): AssertionWarnings
    {
        return $this->warnings;
    }

    public function attachBaseline(Variant $baselineVariant): void
    {
        $this->baseline = $baselineVariant;
    }

    public function getBaseline(): ?Variant
    {
        return $this->baseline;
    }
}
