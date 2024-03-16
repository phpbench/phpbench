<?php

namespace PhpBench\Report\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpBench\Expression\Ast\Node;

/**
 * @implements IteratorAggregate<Node>
 */
final class TableRow implements IteratorAggregate, Countable
{
    /**
     * @param array<string, Node> $cells
     */
    private function __construct(private readonly array $cells)
    {
    }

    /**
     * @param array<string, Node> $row
     */
    public static function fromArray(array $row): self
    {
        return new self($row);
    }

    /**
     * @return ArrayIterator<string, Node>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->cells);
    }

    /**
     * @return string[]
     */
    public function keys(): array
    {
        return array_keys($this->cells);
    }

    /**
     * @return array<string, Node>
     */
    public function cells(): array
    {
        return $this->cells;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->cells);
    }
}
