<?php

namespace PhpBench\Report\Model;

final class Table
{
    /**
     * @var TableRow[]
     */
    private $rows;

    /**
     * @param TableRow[] $rows
     */
    private function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public static function fromArray(array $rows): self
    {
        return new self(array_map(function (array $row) {
            return TableRow::fromArray($row);
        }, $rows));
    }
}
