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

namespace PhpBench\Assertion;

use IteratorAggregate;
use PhpBench\Model\Variant;

/**
 * @implements IteratorAggregate<AssertionResult>
 */
abstract class AbstractAssertionResults implements IteratorAggregate, \Countable
{
    /**
     * @var Variant
     */
    private $variant;

    /**
     * @var array<AssertionResult>
     */
    private $results = [];

    /**
     * @param array<AssertionResult> $results
     */
    public function __construct(Variant $variant, array $results = [])
    {
        $this->variant = $variant;

        foreach ($results as $result) {
            $this->add($result);
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->results);
    }

    public function add(AssertionResult $result)
    {
        $this->results[] = $result;
    }

    public function asArray(): array
    {
        return $this->results;
    }

    public function getVariant(): Variant
    {
        return $this->variant;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->results);
    }
}
