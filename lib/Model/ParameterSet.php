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

use Iterator;

/**
 * @implements Iterator<string,mixed>
 */
final class ParameterSet implements Iterator
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @param array<string,mixed> $parameters
     */
    public function __construct(string $name, array $parameters = [])
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * @param array<string,mixed> $parameters
     */
    public static function create(string $name, array $parameters): self
    {
        return new self($name, $parameters);
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @deprecated use getName instead
     */
    public function getIndex(): string
    {
        return $this->name;
    }

    public function toArray()
    {
        return $this->parameters;
    }

    public static function fromArray(string $name, array $parameterSet): ParameterSet
    {
        return new self($name, $parameterSet);
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return current($this->parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        next($this->parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return key($this->parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return false !== current($this->parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        reset($this->parameters);
    }
}
