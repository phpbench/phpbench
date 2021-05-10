<?php

namespace PhpBench\Data;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use PhpBench\Data\DataFrame;
use PhpBench\Data\Func\Partition;
use RuntimeException;
use function array_reduce;
use function array_search;
use function iterator_to_array;

final class DataFrame implements IteratorAggregate
{
    /**
     * @var Series[]
     */
    private $rows;

    /**
     * @var array
     */
    private $columns;

    public function __construct(array $rows, array $columns)
    {
        $this->rows = $rows;
        $this->columns = $columns;
    }

    public static function fromRecords(array $records): self
    {
        $rows = [];
        $columns = null;
        foreach ($records as $index => $record) {
            $keys = array_keys($record);
            if (null === $columns) {
                $columns = $keys;
            }
            if ($keys !== $columns) {
                throw new RuntimeException(sprintf(
                    'Record "%s" was expected to have columns "%s", but it has "%s"',
                    $index, implode('", "', $columns), implode('", "', $keys)
                ));
            }
            $rows[] = new Series(array_values($record));
        }

        return new self($rows, $columns ?? []);
    }

    public function toRecords(): array
    {
        return array_map(function (Series $series) {
            return array_combine($this->columns, $series->toValues());
        }, $this->rows);
    }

    /**
     * @param int|string $index
     */
    public function column($index): Series
    {
        $offset = array_search($index, $this->columns);

        if (false === $offset) {
            throw new RuntimeException(sprintf(
                'Could not find column "%s", known columns "%s"',
                $index, implode('", "', $this->columns)
            ));
        }

        return new Series(array_map(function (Series $series) use ($offset) {
            return $series->value($offset);
        }, $this->rows));
    }

    /**
     * @param int|string $index
     */
    public function row($index): DataFrame
    {
        if (!isset($this->rows[$index])) {
            throw new RuntimeException(sprintf(
                'Could not find row "%s" in data frame with %s row(s)',
                $index, count($this->rows)
            ));
        }

        return new self([$this->rows[$index]], $this->columns);
    }

    /**
     * @return Row[]
     */
    public function rows(): array
    {
        return array_map(function (Series $row) {
            return new Row(array_combine($this->columns, $row->toValues()));
        }, $this->rows);
    }

    public function toValues(): array
    {
        return array_reduce($this->rows, function (array $carry, Series $row) {
            return array_merge($carry, $row->toValues());
        }, []);
    }

    /**
     * @return ArrayIterator<Series>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->rows);
    }

    /**
     * @return string[]
     */
    public function columnNames(): array
    {
        return $this->columns;
    }

    public function partition(array $columns): DataFrames
    {
        return (new Partition())->__invoke($this, $columns);
    }
}

