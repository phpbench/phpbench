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
     * @param array<int,array<string,array<string,array{"type":string,"value":string}>>> $parameterSets
     */
    public static function fromWrappedParameterSetsCollection(array $parameterSets): self
    {
        return new self(...array_map(function (array $parameterSets) {
            return ParameterSets::fromWrappedParameterSets($parameterSets);
        }, $parameterSets));
    }

    /**
     * @param array<int,array<string,array<string,mixed>>> $parameterSets
     */
    public static function fromUnwrappedParameterSetsCollection(array $parameterSets): self
    {
        return new self(...array_map(function (array $parameterSets) {
            return ParameterSets::fromUnwrappedParameterSets($parameterSets);
        }, $parameterSets));
    }

    /**
     * @return ArrayIterator<int, ParameterSets>
     */
    public function getIterator()
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
    public function toUnwrappedParameterSetsCollection(): array
    {
        return array_map(function (ParameterSets $parameterSets) {
            return $parameterSets->toUnwrappedParameterSets();
        }, $this->parameterSets);
    }
}
