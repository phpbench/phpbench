<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Report\Generator\Table;

use RuntimeException;

/**
 * Repesents a *data* table row including any metadata about the row.
 */
final class Row
{
    /**
     * @var array<string,mixed>
     */
    private $formatParams = [];

    /**
     * @var array<Cell>
     */
    private $cells;

    /**
     * @param array<Cell> $row
     */
    public function __construct(array $row)
    {
        $this->cells = array_map(function (Cell $cell) {
            return $cell;
        }, $row);
    }

    /**
     * @return array<string,Cell>
     */
    public function toArray(): array
    {
        return $this->cells;
    }

    /**
     * Merge the given array into the data of this row and
     * return a new instance.
     *
     * @param array<string,mixed> $array
     */
    public function mergeMap(array $array): self
    {
        $array = Row::fromMap($array);

        return $this->newInstance(
            array_merge(
                $this->cells,
                $array->toArray()
            )
        );
    }

    /**
     * Return the format parameters.
     *
     * @return array<string,mixed>
     */
    public function getFormatParams(): array
    {
        return $this->formatParams;
    }

    /**
     * Set the format parameters.
     *
     * @param array<string,mixed> $formatParams
     */
    public function setFormatParams(array $formatParams): void
    {
        $this->formatParams = $formatParams;
    }

    /**
     * Return a new instance of row using the given data but
     * keeping the metadata for this row.
     *
     * @param array<Cell> $cells
     *
     * @return Row
     */
    public function newInstance(array $cells): self
    {
        $duplicate = new self($cells);
        $duplicate->setFormatParams($this->getFormatParams());

        return $duplicate;
    }

    /**
     * Return the cell names for this row.
     *
     * @return array<string>
     */
    public function getNames(): array
    {
        return array_keys($this->toArray());
    }

    public function hasColumn(string $columnName): bool
    {
        return array_key_exists($columnName, $this->cells);
    }

    /**
     * @return mixed
     */
    public function getValue(string $columnName)
    {
        $this->assertColumnExists($columnName);

        return $this->getCell($columnName)->getValue();
    }

    public function getCell(string $columnName): Cell
    {
        $this->assertColumnExists($columnName);

        return $this->cells[$columnName];
    }

    public function removeColumn(string $columnName): void
    {
        unset($this->cells[$columnName]);
    }

    /**
     * @param mixed $value
     */
    public function setValue(string $columnName, $value): void
    {
        if (!isset($this->cells[$columnName])) {
            $this->cells[$columnName] = Cell::fromValue($value);

            return;
        }
        $this->cells[$columnName]->setValue($value);
    }

    /**
     * @param array<string,mixed> $map
     */
    public static function fromMap(array $map): self
    {
        return new self(array_map(function ($value) {
            return Cell::fromValue($value);
        }, $map));
    }

    private function assertColumnExists(string $columnName): void
    {
        if (array_key_exists($columnName, $this->cells)) {
            return;
        }

        throw new RuntimeException(sprintf(
            'Column "%s" does not exist in row with columns "%s"',
            $columnName, implode('", "', array_keys($this->cells))
        ));
    }

    public function __clone()
    {
        $this->cells = array_map(function (Cell $cell) {
            return clone $cell;
        }, $this->cells);
    }

    public function addCell(string $columnName, Cell $cell): void
    {
        $this->cells[$columnName] = $cell;
    }
}
