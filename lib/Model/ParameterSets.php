<?php

namespace PhpBench\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpBench\Model\Exception\InvalidParameterSets;

/**
 * @implements IteratorAggregate<string, ParameterSet>
 */
final class ParameterSets implements IteratorAggregate, Countable
{
    /**
     * @var array<string, ParameterSet>
     */
    private $parameterSets;

    /**
     * @param array<string, ParameterSet> $parameterSets
     */
    private function __construct(array $parameterSets)
    {
        $this->parameterSets = $parameterSets;
    }

    /**
     * @param array<string,array<string,array{"type":string,"value":string}>> $parameterSets
     */
    public static function fromUnsafeArray(array $parameterSets): self
    {
        return new self(array_combine(array_keys($parameterSets), array_map(function ($parameterSet, string $name) {
            if (!is_array($parameterSet)) {
                throw new InvalidParameterSets(sprintf(
                    'Each parameter set must be an array, got "%s"',
                    /** @phpstan-ignore-next-line */
                    is_object($parameterSet) ? get_class($parameterSet) : gettype($parameterSet)
                ));
            }

            return ParameterSet::fromUnsafeArray($name, $parameterSet);
        }, $parameterSets, array_keys($parameterSets))));
    }

    /**
     * @param array<string,array<string,mixed>> $parameterSets
     */
    public static function fromArray(array $parameterSets): self
    {
        return new self(array_combine(array_keys($parameterSets), array_map(function ($parameterSet, string $name) {
            return ParameterSet::fromArray($name, $parameterSet);
        }, $parameterSets, array_keys($parameterSets))));
    }

    /**
     * @return ArrayIterator<string, ParameterSet>
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
        return new self([]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_combine(
            array_map(function (ParameterSet $parameterSet) {
                return $parameterSet->getName();
            }, $this->parameterSets),
            array_map(function (ParameterSet $parameterSet) {
                return $parameterSet->toUnserializedArray();
            }, $this->parameterSets)
        );
    }
}
