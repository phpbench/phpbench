<?php

namespace PhpBench\Data;

use ReturnTypeWillChange;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use PHPUnit\Framework\MockObject\BadMethodCallException;
use RuntimeException;

/**
 * @implements IteratorAggregate<string, scalar|null>
 * @implements ArrayAccess<string, scalar|null>
 */
final class Row implements IteratorAggregate, ArrayAccess
{
    /**
     * @param array<string, scalar|null> $map
     */
    public function __construct(private array $map)
    {
    }

    /**
     * @return scalar|null
     */
    public function get(string $column)
    {
        if (!array_key_exists($column, $this->map)) {
            throw new RuntimeException(sprintf(
                'Row does not have column "%s", it has columns "%s"',
                $column,
                implode('", "', array_keys($this->map))
            ));
        }

        return $this->map[$column];
    }

    /**
     * @return ArrayIterator<string,mixed>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->map);
    }

    public function toSeries(): Series
    {
        return new Series(array_values($this->map));
    }

    /**
     * @return array<string, scalar|null>
     */
    public function toRecord(): array
    {
        return $this->map;
    }

    /**
     * @param string[] $resolvedNames
     */
    public function only(array $resolvedNames): self
    {
        return new self(array_combine($resolvedNames, array_map(function (string $column) {
            return $this->get($column);
        }, $resolvedNames)));
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->map[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    #[ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('Not implemented');
    }
}
