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
     * @var TableRow[]
     */
    private $rows;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string[]
     */
    private $headers;

    /**
     * @var array<string,TableColumnGroup>
     */
    private $columnGroups;

    /**
     * @param Node[]|string[]|null $headers
     * @param TableRow[] $rows
     * @param TableColumnGroup[] $columnGroups
     * @param string[] $headers
     */
    public function __construct(array $rows, ?array $headers, ?string $title, array $columnGroups = [])
    {
        $this->rows = $rows;
        $this->title = $title;
        $this->headers = $headers;
        $this->columnGroups = $columnGroups;
    }

    /**
     * @deprecated to be removed in 2.0. Use TableBuilder.
     *
     * @param array<int|string,array<int|string,mixed>> $rows
     */
    public static function fromRowArray(array $rows, ?string $title = null): self
    {
        $headers = [];

        foreach ($rows as $row) {
            $headers = array_keys($row);
        }

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
     * {@inheritDoc}
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
     * @return string[]
     */
    public function headers(): ?array
    {
        return $this->headers;
    }

    /**
     * @retrun TableColumnGroup[]
     */
    public function columnGroups(): array
    {
        return $this->columnGroups;
    }
}
