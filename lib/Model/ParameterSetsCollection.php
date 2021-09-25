<?php

namespace PhpBench\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, ParameterSets>
 */
final class ParameterSetsCollection implements IteratorAggregate, Countable
{
    /**
     * @var ParameterSets[]
     */
    private $parameterSets;

    public function __construct(ParameterSets ...$parameterSets)
    {
        $this->parameterSets = $parameterSets;
    }

    /**
     * @param array<array<array<string>>> $parameterSets
     */
    public static function fromSerializedParameterSetsCollection(array $parameterSets): self
    {
        return new self(...array_map(function (array $parameterSets) {
            return ParameterSets::fromSerializedParameterSets($parameterSets);
        }, $parameterSets));
    }

    /**
     * @param array<int,array<string,array<string,mixed>>> $parameterSets
     */
    public static function fromUnserializedParameterSetsCollection(array $parameterSets): self
    {
        return new self(...array_map(function (array $parameterSets) {
            return ParameterSets::fromUnserializedParameterSets($parameterSets);
        }, $parameterSets));
    }

    /**
     * @return ArrayIterator<int, ParameterSets>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->parameterSets);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->parameterSets);
    }

    public static function empty(): self
    {
        return new self(ParameterSets::empty());
    }

    /**
     * @return array<int, array<string, array<string, mixed>>>
     */
    public function toUnserializedParameterSetsCollection(): array
    {
        return array_map(function (ParameterSets $parameterSets) {
            return $parameterSets->toUnserializedParameterSets();
        }, $this->parameterSets);
    }
}
