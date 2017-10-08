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
            $this->add($failure);
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
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->failures);
    }
}
