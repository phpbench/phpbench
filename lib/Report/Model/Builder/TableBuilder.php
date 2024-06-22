<?php

namespace PhpBench\Report\Model\Builder;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Report\Model\Table;
use PhpBench\Report\Model\TableColumnGroup;
use PhpBench\Report\Model\TableRow;

class TableBuilder
{
    private ?string $title = null;

    /**
     * @var TableRow[]
     */
    private array $rows = [];

    /**
     * @deprecated
     *
     * @var Node[]
     */
    private ?array $headers = null;

    /**
     * @var TableColumnGroup[]
     */
    private array $columnGroups = [];


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
     * @deprecated
     *
     * @param string[] $headers
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_map(static function (string $header) {
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

    /**
     * @param tableRowArray $row
     */
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
