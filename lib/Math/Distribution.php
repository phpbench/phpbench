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

namespace PhpBench\Math;

use InvalidArgumentException;
use LogicException;
use RuntimeException;
use ArrayIterator;
use ReturnTypeWillChange;
use BadMethodCallException;
use ArrayAccess;
use IteratorAggregate;

/**
 * Represents a population of samples.
 *
 * Lazily Provides summary statistics, also traversable.
 *
 * @implements IteratorAggregate<string, float|int>
 * @implements ArrayAccess<string, float|int>
 *
 * @phpstan-type Stats array{min: float|int, max: float|int, sum: float|int, stdev: float|int, mean: float|int, mode: float|int, variance: float|int, rstdev: float|int }
 * @phpstan-type Closures array{min: callable():(float|int), max: callable():(float|int), sum: callable():(float|int), stdev: callable():(float|int), mean: callable():(float|int), mode: callable():(float|int), variance: callable():(float|int), rstdev: callable():(float|int) }
 */
class Distribution implements IteratorAggregate, ArrayAccess
{
    /** @var Closures */
    private array $closures;

    /**
     * @param array<float|int> $samples
     * @param array{min?: float|int, max?: float|int, sum?: float|int, stdev?: float|int, mean?: float|int, mode?: float|int, variance?: float|int, rstdev?: float|int } $stats
     */
    public function __construct(private array $samples, private array $stats = [])
    {
        if (count($samples) < 1) {
            throw new LogicException(
                'Cannot create a distribution with zero samples.'
            );
        }

        $this->closures = [
            'min' => fn () => min($this->samples),
            'max' => fn () => max($this->samples),
            'sum' => fn () => array_sum($this->samples),
            'stdev' => fn () => Statistics::stdev($this->samples),
            'mean' => fn () => Statistics::mean($this->samples),
            'mode' => fn () => Statistics::kdeMode($this->samples),
            'variance' => fn () => Statistics::variance($this->samples),
            'rstdev' => function () {
                $mean = $this->getMean();

                return $mean ? $this->getStdev() / $mean * 100 : 0;
            },
        ];

        if ($diff = array_diff(array_keys($this->stats), array_keys($this->closures))) {
            throw new RuntimeException(sprintf(
                'Unknown pre-computed stat(s) encountered: "%s"',
                implode('", "', $diff)
            ));
        }
    }

    /**
     * @return float|int
     */
    public function getMin()
    {
        return $this->getStat('min');
    }

    /**
     * @return float|int
     */
    public function getMax()
    {
        return $this->getStat('max');
    }

    /**
     * @return float|int
     */
    public function getSum()
    {
        return $this->getStat('sum');
    }

    /**
     * @return float|int
     */
    public function getStdev()
    {
        return $this->getStat('stdev');
    }

    /**
     * @return float|int
     */
    public function getMean()
    {
        return $this->getStat('mean');
    }

    /**
     * @return float|int
     */
    public function getMode()
    {
        return $this->getStat('mode');
    }

    /**
     * @return float|int
     */
    public function getRstdev()
    {
        return $this->getStat('rstdev');
    }

    /**
     * @return float|int
     */
    public function getVariance()
    {
        return $this->getStat('variance');
    }

    /**
     * @return ArrayIterator<string, float|int>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getStats());
    }

    /**
     * @return Stats
     */
    public function getStats(): array
    {
        return [
            'min' => $this->getMin(),
            'max' => $this->getMax(),
            'sum' => $this->getSum(),
            'stdev' => $this->getStdev(),
            'mean' => $this->getMean(),
            'mode' => $this->getMode(),
            'variance' => $this->getVariance(),
            'rstdev' => $this->getRstdev(),
        ];
    }

    /**
     * @param key-of<Closures> $name
     */
    private function getStat(string $name): float|int
    {
        if (!isset($this->closures[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Unknown stat "%s", known stats: "%s"',
                $name,
                implode('", "', array_keys($this->closures))
            ));
        }

        return $this->stats[$name] ??= $this->closures[$name]();
    }

    /**
     * {@inheritdoc}
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return isset($this->closures[$offset]);
    }

    /**
     * {@inheritdoc}
     *
     * @param key-of<Closures> $offset
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getStat($offset);
    }

    /**
     * {@inheritdoc}
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException('Distribution is read-only');
    }

    /**
     * {@inheritdoc}
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('Distribution is read-only');
    }
}
