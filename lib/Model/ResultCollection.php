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

use RuntimeException;
use InvalidArgumentException;

/**
 * Represents the result of a single iteration executed by an executor.
 */
class ResultCollection
{
    /**
     * @var array<class-string<ResultInterface>, ResultInterface>
     */
    private array $results = [];

    /**
     * @param ResultInterface[] $results
     */
    public function __construct(array $results = [])
    {
        foreach ($results as $result) {
            $this->setResult($result);
        }
    }

    /**
     * Add a result to the collection.
     *
     * Only one result per class is permitted.
     *
     */
    public function setResult(ResultInterface $result): void
    {
        $class = $result::class;
        $this->results[$class] = $result;
    }

    /**
     * Return true if there is a result for the given class name.
     *
     * @param class-string<ResultInterface> $class
     */
    public function hasResult(string $class): bool
    {
        return isset($this->results[$class]);
    }

    /**
     * Return the result of the given class, throw an exception
     * if it does not exist.
     *
     * @template T of ResultInterface
     *
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws RuntimeException
     */
    public function getResult(string $class): ResultInterface
    {
        if (!isset($this->results[$class])) {
            throw new RuntimeException(sprintf(
                'Result of class "%s" has not been set',
                $class
            ));
        }

        return $this->results[$class];
    }

    /**
     * Return the named metric for the given result class.
     *
     * @param class-string<ResultInterface> $class
     *
     * @return float|int
     *
     * @throws InvalidArgumentException
     */
    public function getMetric(string $class, string $metric)
    {
        $metrics = $this->getResult($class)->getMetrics();

        if (!isset($metrics[$metric])) {
            throw new InvalidArgumentException(sprintf(
                'Unknown metric "%s" for result class "%s". Available metrics: "%s"',
                $metric,
                $class,
                implode('", "', array_keys($metrics))
            ));
        }

        return $metrics[$metric];
    }

    /**
     * Return the named metric or the default value if the *result class* has
     * not been set.
     *
     * If the metric does not exist but the class *does* exist then there is
     * clearly a problem and we should allow an error to be thrown.
     *
     * @template TDefault
     *
     * @param class-string<ResultInterface> $class
     * @param TDefault $default
     *
     * @return int|float|TDefault
     */
    public function getMetricOrDefault(string $class, string $metric, $default = null)
    {
        if (false === $this->hasResult($class)) {
            return $default;
        }

        return $this->getMetric($class, $metric);
    }

    /**
     * Return all results.
     *
     * @return array<class-string<ResultInterface>, ResultInterface>
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
