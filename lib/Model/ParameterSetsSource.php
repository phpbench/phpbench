<?php

namespace PhpBench\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpBench\Model\Exception\InvalidParameterSets;

/**
 * @implements IteratorAggregate<ParameterSets>
 */
final class ParameterSetsSource implements IteratorAggregate, Countable
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
    public static function fromUnsafeArray(array $parameterSets): self
    {
        return new self(...array_map(function (array $parameterSets) {
            return ParameterSets::fromUnsafeArray($parameterSets);
        }, $parameterSets));
    }

    /**
     * @param array<int,array<string,array<string,mixed>>> $parameterSets
     */
    public static function fromArray(array $parameterSets): self
    {
        return new self(...array_map(function (array $parameterSets) {
            return ParameterSets::fromArray($parameterSets);
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

    public function toArray(): array
    {
        return array_map(function (ParameterSets $parameterSets) {
            return $parameterSets->toArray();
        }, $this->parameterSetsSources);
    }
}
