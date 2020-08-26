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

use IteratorAggregate;

/**
 * Essentially this class represents a single exception (the "top"
 * exception) and any parent exceptions of it.
 *
 * It is also linked to the variant which encountered the error.
 *
 * @implements IteratorAggregate<Error>
 */
class ErrorStack implements IteratorAggregate
{
    /**
     * @var Error[]
     */
    private $errors;

    /**
     * @var Variant
     */
    private $variant;

    /**
     * @param Variant $variant
     * @param Error[] $errors
     */
    public function __construct(Variant $variant, array $errors)
    {
        $this->variant = $variant;
        $this->errors = $errors;
    }

    public function getVariant()
    {
        return $this->variant;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getTop()
    {
        return reset($this->errors);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->errors);
    }
}
