<?php

namespace PhpBench\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<ParameterSets>
 */
final class ParameterSetsCollection implements IteratorAggregate, Countable
{
    /**
     * @var ParameterSets[]
     */
    private $parameterSetsSources;

    public function __construct(ParameterSets ...$parameterSetsSources)
    {
        $this->parameterSetsSources = $parameterSetsSources;
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
     * @return ArrayIterator<mixed, ParameterSets>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->parameterSetsSources);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->parameterSetsSources);
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
        }, $this->parameterSetsSources);
    }
}
