<?php

namespace PhpBench\Data;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use PHPUnit\Framework\MockObject\BadMethodCallException;
use RuntimeException;

/**
 * @implements IteratorAggregate<string,mixed>
 * @implements ArrayAccess<string,mixed>
 */
final class Row implements IteratorAggregate, ArrayAccess
{
    /**
     * @var array<string,mixed>
     */
    private $map;

    /**
     * @param array<string,mixed> $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * @return scalarOrNull
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
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->map[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('Not implemented');
    }
}
