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
use Countable;
use IteratorAggregate;

/**
 * Essentially this class represents a single exception (the "top"
 * exception) and any parent exceptions of it.
 *
 * It is also linked to the variant which encountered the error.
 *
 * @implements IteratorAggregate<Error>
 */
class ErrorStack implements IteratorAggregate, Countable
{
    /**
     * @param Error[] $errors
     */
    public function __construct(private readonly Variant $variant, private array $errors)
    {
    }

    public function getVariant(): Variant
    {
        return $this->variant;
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return false|Error
     */
    public function getTop()
    {
        return reset($this->errors);
    }

    /**
     * @return ArrayIterator<array-key, Error>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->errors);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->errors);
    }
}
