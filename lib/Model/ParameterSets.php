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
     * @param array<array<string>> $parameterSets
     */
    public static function fromSerializedParameterSets(array $parameterSets): self
    {
        return new self(array_combine(array_keys($parameterSets), array_map(function ($parameterSet, string $name) {
            self::assertParameterSet($parameterSet);

            return ParameterSet::fromSerializedParameters($name, $parameterSet);
        }, $parameterSets, array_keys($parameterSets))));
    }

    /**
     * @param array<string,array<string,mixed>> $parameterSets
     */
    public static function fromUnserializedParameterSets(array $parameterSets): self
    {
        return new self(array_combine(array_keys($parameterSets), array_map(function ($parameterSet, string $name) {
            self::assertParameterSet($parameterSet);

            return ParameterSet::fromUnserializedValues($name, $parameterSet);
        }, $parameterSets, array_keys($parameterSets))));
    }

    /**
     * @return ArrayIterator<string, ParameterSet>
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
        return new self([]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function toUnserializedParameterSets(): array
    {
        return array_combine(
            array_map(function (ParameterSet $parameterSet) {
                return $parameterSet->getName();
            }, $this->parameterSets),
            array_map(function (ParameterSet $parameterSet) {
                return $parameterSet->toUnserializedParameters();
            }, $this->parameterSets)
        );
    }

    /**
     * @param mixed $parameterSet
     */
    private static function assertParameterSet($parameterSet): void
    {
        if (is_array($parameterSet)) {
            return;
        }

        throw new InvalidParameterSets(sprintf(
            'Each parameter set must be an array, got "%s"',
            is_object($parameterSet) ? get_class($parameterSet) : gettype($parameterSet)
        ));
    }
}
