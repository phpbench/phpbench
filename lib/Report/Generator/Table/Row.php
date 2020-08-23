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
     * @var array
     */
    private $formatParams = [];

    /**
     * @var array
     */
    private $row;

    public function __construct(array $row)
    {
        $this->row = $row;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return $this->row;
    }

    /**
     * Merge the given array into the data of this row and
     * return a new instance.
     *
     * @param array $array
     *
     * @return Row
     */
    public function merge(array $array)
    {
        return $this->newInstance(
            array_merge(
                $this->row,
                $array
            )
        );
    }

    /**
     * Return the format parameters.
     *
     * @return array
     */
    public function getFormatParams()
    {
        return $this->formatParams;
    }

    /**
     * Set the format parameters.
     *
     * @param array $formatParams
     */
    public function setFormatParams($formatParams)
    {
        $this->formatParams = $formatParams;
    }

    /**
     * Return a new instance of row using the given data but
     * keeping the metadata for this row.
     *
     * @param array $array
     *
     * @return Row
     */
    public function newInstance(array $array)
    {
        $duplicate = new self($array);
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
        return array_key_exists($columnName, $this->row);
    }

    /**
     * @return mixed
     */
    public function getValue(string $columnName)
    {
        if (!array_key_exists($columnName, $this->row)) {
            throw new RuntimeException(sprintf(
                'Column "%s" does not exist in row with columns "%s"',
                $columnName, implode('", "', array_keys($this->row))
            ));
        }

        return $this->row[$columnName];
    }

    public function removeColumn(string $columnName): void
    {
        unset($this->row[$columnName]);
    }

    /**
     * @param mixed $value 
     */
    public function setValue(string $columnName, $value): void
    {
        $this->row[$columnName] = $value;
    }

    public static function fromMap(array $array): self
    {
        return new self(array_map(function ($value) {
            return Cell::fromValue($value);
        }, $array));
    }
}
