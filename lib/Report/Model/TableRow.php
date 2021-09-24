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
     * @var array<string,Node>
     */
    private $cells;

    /**
     * @param array<string,Node> $cells
     */
    private function __construct(array $cells)
    {
        $this->cells = $cells;
    }

    public static function fromArray(array $row): self
    {
        return new self(array_map(function (Node $node) {
            return $node;
        }, $row));
    }

    /**
     * {@inheritDoc}
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
