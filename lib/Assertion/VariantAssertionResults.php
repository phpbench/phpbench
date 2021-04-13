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

use ArrayIterator;
use IteratorAggregate;
use PhpBench\Model\Variant;

/**
 * @implements IteratorAggregate<AssertionResult>
 */
class VariantAssertionResults implements IteratorAggregate, \Countable
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

    /**
     * @return ArrayIterator<int,AssertionResult>
     */
    public function getIterator(): ArrayIterator
    {
        return new \ArrayIterator($this->results);
    }

    public function add(AssertionResult $result): void
    {
        $this->results[] = $result;
    }

    /**
     * @return AssertionResult[]
     */
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
    public function count(): int
    {
        return count($this->results);
    }

    public function hasFailures(): bool
    {
        foreach ($this->results as $result) {
            if ($result->isFail()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return self<AssertionResult>
     */
    public function failures(): self
    {
        return new self($this->variant, array_filter($this->results, function (AssertionResult $result) {
            return $result->isFail();
        }));
    }

    /**
     * @return self<AssertionResult>
     */
    public function tolerations(): self
    {
        return new self($this->variant, array_filter($this->results, function (AssertionResult $result) {
            return $result->isTolerated();
        }));
    }
}
