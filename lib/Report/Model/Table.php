<?php

namespace PhpBench\Report\Model;

use ArrayIterator;
use IteratorAggregate;
use PhpBench\Expression\Ast\Node;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\Builder\TableBuilder;

/**
 * @implements IteratorAggregate<TableRow>
 */
final class Table implements IteratorAggregate, ComponentInterface
{
    /**
     * @param Node[]|null $headers
     * @param TableRow[] $rows
     * @param TableColumnGroup[] $columnGroups
     */
    public function __construct(private readonly array $rows, private readonly ?array $headers, private readonly ?string $title, private readonly array $columnGroups = [])
    {
    }

    /**
     * @deprecated to be removed in 2.0. Use TableBuilder.
     *
     * @param tableRowArray[] $rows
     */
    public static function fromRowArray(array $rows, ?string $title = null): self
    {
        return TableBuilder::create()
            ->addRowsFromArray($rows)
            ->withTitle($title)
            ->build();
    }

    public function title(): ?string
    {
        return $this->title;
    }

    /**
     * @return ArrayIterator<array-key, TableRow>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->rows);
    }

    /**
     * @return string[]
     */
    public function columnNames(): array
    {
        foreach ($this->rows as $row) {
            return $row->keys();
        }

        return [];
    }

    /**
     * @return TableRow[]
     */
    public function rows(): array
    {
        return $this->rows;
    }

    /**
     * @deprecated
     *
     * @return Node[]|null
     */
    public function headers(): ?array
    {
        return $this->headers;
    }

    /**
     * @return TableColumnGroup[]
     */
    public function columnGroups(): array
    {
        return $this->columnGroups;
    }
}
