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
     * @var Parameters
     */
    private $parameters;

    /**
     */
    private function __construct(string $name, Parameters $parameters)
    {
        $this->name = $name;
        $this->parameters = $parameters;
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

    /**
     * @return array<array<string,mixed>>
     */
    public function toArray(): array
    {
        return $this->parameters->toArray();
    }

    public static function fromContainers(string $name, ParameterContainer ...$parameterContainers): self
    {
        return new self($name, Parameters::fromContainers($parameterContainers));
    }

    /**
     * @param array<string,array{"type":string,"value":string}> $parameterSet
     */
    public static function fromUnsafeArray(string $name, array $parameters): ParameterSet
    {
        return new self($name, Parameters::fromUnsafeArray($parameters));
    }

    /**
     */
    public static function fromArray(string $name, array $parameters): self
    {
        return new self($name, Parameters::fromArray($parameters));
    }

    public function toUnserializedArray(): array
    {
        return $this->parameters->toUnserializedArray();
    }

    /**
     * @return Parameters|false
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
     * @return string
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

    public function serialize(): array
    {
        return $this->parameters->toSerializedArray();
    }
}
