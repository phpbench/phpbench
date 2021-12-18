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
use RuntimeException;

/**
 * @implements IteratorAggregate<Suite>
 */
/** final */class SuiteCollection implements IteratorAggregate
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
     */
    public function addSuite(Suite $suite): void
    {
        $this->suites[] = $suite;
    }

    /**
     * Merge another collection into this one.
     */
    public function mergeCollection(SuiteCollection $collection): self
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
            if (!$suiteVariant = $suite->findVariant(
                $variant->getSubject()->getBenchmark()->getClass(),
                $variant->getSubject()->getName(),
                $variant->getParameterSet()->getName()
            )) {
                continue;
            }

            return $suiteVariant;
        }

        return null;
    }

    public function firstOnly(): self
    {
        if (!isset($this->suites[0])) {
            return new SuiteCollection([]);
        }

        return new self([
            $this->suites[0]
        ]);
    }

    /**
     * @param string[] $subjectPatterns
     * @param string[] $variantPatterns
     */
    public function filter(array $subjectPatterns, array $variantPatterns): self
    {
        $new = clone $this;
        $new->suites = array_map(function (Suite $suite) use ($subjectPatterns, $variantPatterns) {
            return $suite->filter($subjectPatterns, $variantPatterns);
        }, $this->suites);

        return $new;
    }

    public function first(): Suite
    {
        if (empty($this->suites)) {
            throw new RuntimeException(
                'Suite collection is empty, cannot get first'
            );
        }

        $suite = reset($this->suites);

        return $suite;
    }
}
