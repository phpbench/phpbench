<?php

namespace PhpBench\Report\Model;

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<TableRow>
 */
final class Table implements IteratorAggregate
{
    /**
     * @var TableRow[]
     */
    private $rows;

    /**
     * @var string
     */
    private $title;

    /**
     * @param TableRow[] $rows
     */
    private function __construct(array $rows, string $title)
    {
        $this->rows = $rows;
        $this->title = $title;
    }

    public static function fromArray(array $rows, string $title): self
    {
        return new self(array_map(function (array $row) {
            return TableRow::fromArray($row);
        }, $rows), $title);
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->rows);
    }

    public function columnNames()
    {
        foreach ($this->rows as $row) {
            return $row->keys();
        }
    }

    public function rows(): array
    {
        return $this->rows;
    }
}
