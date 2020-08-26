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

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<Suite>
 */
class SuiteCollection implements IteratorAggregate
{
    /**
     * @var Suite[]
     */
    private $suites;

    /**
     * @param Suite[] $suites
     */
    public function __construct(array $suites = [])
    {
        $this->suites = $suites;
    }

    /**
     * Return the suites.
     *
     * @return Suite[]
     */
    public function getSuites(): array
    {
        return $this->suites;
    }

    /**
     * Add a suite to the collection.
     *
     * @param Suite $suite
     */
    public function addSuite(Suite $suite): void
    {
        $this->suites[] = $suite;
    }

    /**
     * Merge another collection into this one.
     *
     * @param SuiteCollection $collection
     */
    public function mergeCollection(self $collection): self
    {
        foreach ($collection->getSuites() as $suite) {
            $this->addSuite($suite);
        }

        return $this;
    }

    /**
     * @return ArrayIterator<string,Suite>
     */
    public function getIterator(): ArrayIterator
    {
        return new \ArrayIterator($this->suites);
    }

    public function findBaselineForVariant(Variant $variant): ?Variant
    {
        foreach (array_reverse($this->suites) as $suite) {
            if (!$variant = $suite->findVariant(
                $variant->getSubject()->getBenchmark()->getClass(),
                $variant->getSubject()->getName(),
                $variant->getParameterSet()->getName()
            )) {
                continue;
            }

            return $variant;
        }

        return null;
    }
}
