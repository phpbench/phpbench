<?php

namespace PhpBench\Data;

use function array_map;
use function array_reduce;
use function array_search;
use ArrayAccess;
use ArrayIterator;
use Closure;
use IteratorAggregate;
use PhpBench\Data\Func\Partition;
use PHPUnit\Framework\MockObject\BadMethodCallException;
use RuntimeException;

final class DataFrame implements IteratorAggregate, ArrayAccess
{
    /**
     * @var Series[]
     */
    private $series;

    /**
     * @var array
     */
    private $columns;

    public function __construct(array $series, array $columns)
    {
        $this->series = $series;
        $this->columns = array_values($columns);
    }

    /**
     * @param Series[] $rows
     * @param string[] $columns
     */
    public static function fromRowArray(array $rows, array $columns): self
    {
        return new self(array_map(function (array $row) {
            return new Series($row);
        }, $rows), $columns);
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
        }, $this->series);
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
        }, $this->series));
    }

    /**
     * @param int|string $index
     */
    public function row($index): Row
    {
        if (!isset($this->series[$index])) {
            throw new RuntimeException(sprintf(
                'Could not find row "%s" in data frame with %s row(s)',
                $index, count($this->series)
            ));
        }

        return new Row(array_combine($this->columns, $this->series[$index]->toValues()));
    }

    /**
     * @return Row[]
     */
    public function rows(): array
    {
        return array_map(function (Series $series) {
            return new Row(array_combine($this->columns, $series->toValues()));
        }, $this->series);
    }

    public function toValues(): array
    {
        return array_reduce($this->series, function (array $carry, Series $series) {
            return array_merge($carry, $series->toValues());
        }, []);
    }

    /**
     * @return ArrayIterator<Series>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->series);
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

    /**
     * @return array<string, array<mixed>>
     */
    public function columnValues(): array
    {
        $values = array_combine($this->columns, array_fill(0, count($this->columns), []));

        foreach ($this->columns as $index => $name) {
            foreach ($this->series as $series) {
                $value = $series->value($index);
                $values[$name][] = $value;
            }
        }

        return $values;
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function nonNullColumnValues(): array
    {
        return array_map(function (array $values) {
            return array_filter($values, function ($value) {
                return $value !== null;
            });
        }, $this->columnValues());
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return in_array($offset, $this->columns);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->column($offset)->toValues();
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function filter(Closure $closure): self
    {
        return new self(array_values(array_map(function (Row $row) {
            return $row->toSeries();
        }, array_filter($this->rows(), $closure))), $this->columns);
    }
}
