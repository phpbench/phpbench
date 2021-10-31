<?php

namespace PhpBench\Report\Model\Builder;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Report\Model\Table;
use PhpBench\Report\Model\TableColumnGroup;
use PhpBench\Report\Model\TableRow;

class TableBuilder
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var TableRow[]
     */
    private $rows = [];

    /**
     * @var Node[]|string[]|null
     */
    private $headers;

    /**
     * @var TableColumnGroup[]
     */
    private $columnGroups = [];


    final private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function withTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param (string[]|Node[]) $headers
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_map(function (string $header) {
            return PhpValueFactory::fromValue($header);
        }, $headers);

        return $this;
    }

    public function addRow(TableRow $row): self
    {
        $this->rows[] = $row;

        return $this;
    }

    /**
     * @param tableRowArray[] $rows
     */
    public function addRowsFromArray(array $rows): self
    {
        foreach ($rows as $row) {
            $this->addRowArray($row);
        }

        return $this;
    }

    public function addRowArray(array $row): self
    {
        $row = array_map(function ($value) {
            if (!$value instanceof Node) {
                return PhpValueFactory::fromValue($value);
            }

            return $value;
        }, $row);
        $this->rows[] = TableRow::fromArray($row);

        return $this;
    }

    /**
     * @param TableColumnGroup[] $groups
     */
    public function addGroups(array $groups): self
    {
        foreach ($groups as $group) {
            $this->columnGroups[] = $group;
        }

        return $this;
    }

    public function build(): Table
    {
        return new Table($this->rows, $this->headers, $this->title, $this->columnGroups);
    }
}
