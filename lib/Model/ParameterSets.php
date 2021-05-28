<?php

namespace PhpBench\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpBench\Model\Exception\InvalidParameterSets;

final class ParameterSets implements IteratorAggregate, Countable
{
    /**
     * @var ParameterSet[]
     */
    private $parameterSets;

    public function __construct(ParameterSet ...$parameterSets)
    {
        $this->parameterSets = $parameterSets;
    }

    public static function fromArray(array $parameterSets): self
    {
        return new self(...array_map(function ($parameterSet, string $name) {
            if (!is_array($parameterSet)) {
                throw new InvalidParameterSets(sprintf(
                    'Each parameter set must be an array, got "%s"',
                    is_object($parameterSet) ? get_class($parameterSet) : gettype($parameterSet)
                ));
            }

            return ParameterSet::fromArray($name, $parameterSet);
        }, $parameterSets, array_keys($parameterSets)));
    }

    /**
     * {@inheritDoc}
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
        return new self(ParameterSet::fromArray('default',[]));
    }

    public function toArray(): array
    {
        return array_map(function (ParameterSet $parameterSet) {
            return $parameterSet->toArray();
        }, $this->parameterSets);
    }
}
