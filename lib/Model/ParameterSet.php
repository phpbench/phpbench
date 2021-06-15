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
     * @var Parameters[]
     */
    private $parameterSet;

    /**
     * @param Parameters[] $parameters
     */
    private function __construct(string $name, array $parameters)
    {
        $this->name = $name;
        $this->parameterSet = $parameters;
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
        return array_map(function (Parameters $parameters) {
            return $parameters->toUnserializedArray();
        }, $this->parameterSet);
    }

    /**
     * @param array<string,array{"type":string,"value":string}> $parameterSet
     */
    public static function fromUnsafeArray(string $name, array $parameterSet): ParameterSet
    {
        return new self($name, array_map(function (array $parameters) {
            return Parameters::fromUnsafeArray($parameters);
        }, $parameterSet));
    }

    /**
     * @param array<string, array<string,mixed>> $parameterSet
     */
    public static function fromArray(string $name, array $parameterSet): self
    {
        return new self($name, array_map(function (array $parameters) {
            return Parameters::fromArray($parameters);
        }, $parameterSet));
    }

    /**
     * @return Parameters|false
     */
    public function current()
    {
        return current($this->parameterSet);
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        next($this->parameterSet);
    }

    /**
     * @return string
     */
    public function key()
    {
        return key($this->parameterSet);
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return false !== current($this->parameterSet);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        reset($this->parameterSet);
    }

    public function serialize(): string
    {
        return serialize($this->parameterSet);
    }
}
