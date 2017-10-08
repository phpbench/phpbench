<?php

namespace PhpBench\Assertion;

use PhpBench\Model\Variant;

class AssertionFailures implements \IteratorAggregate, \Countable
{
    /**
     * @var Variant
     */
    private $variant;

    /**
     * @var array
     */
    private $failures = [];

    public function __construct(Variant $variant, array $failures = [])
    {
        $this->variant = $variant;

        foreach ($failures as $failure) {
            $this->addFailure($failure);
        }
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->failures);
    }

    public function add(AssertionFailure $failure)
    {
        $this->failures[] = $failure;
    }

    public function asArray(): array
    {
        return $this->failures;
    }

    public function getVariant(): Variant
    {
        return $this->variant;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->failures);
    }
}

