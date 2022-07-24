<?php

namespace PhpBench\Data;

use ArrayAccess;
use ArrayIterator;
use Closure;
use IteratorAggregate;
use PhpBench\Data\Exception\ColumnDoesNotExist;
use PhpBench\Data\Func\Partition;
use PHPUnit\Framework\MockObject\BadMethodCallException;
use RuntimeException;

use function array_map;
use function array_reduce;
use function array_search;

/**
 * @implements IteratorAggregate<Series>
 * @implements ArrayAccess<string,mixed[]>
 */
final class DataFrame implements IteratorAggregate, ArrayAccess
{
    /**
     * @var Series[]
     */
    private $rows;

    /**
     * @var string[]
     */
    private $columns;

    /**
     * @param Series[] $rows
     * @param string[] $columns
     */
    public function __construct(array $rows, array $columns)
    {
        $this->rows = array_map(function (Series $rows, int $index) use ($columns) {
            if (count($rows) !== count($columns)) {
                throw new RuntimeException(sprintf(
                    'Row %s has only %s value(s), but %s column names given',
                    $index,
                    count($rows),
                    count($columns)
                ));
            }

            return $rows;
        }, $rows, array_keys($rows));
        $this->columns = array_values($columns);
    }

    /**
     * @param array<int, array<int|string,mixed>> $rows
     * @param string[] $columns
     */
    public static function fromRowSeries(array $rows, array $columns): self
    {
        return new self(array_map(function (array $row) {
            return new Series($row);
        }, $rows), $columns);
    }

    /**
     * @param array<int, array<string,mixed>> $records
     */
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
                    $index,
                    implode('", "', $columns),
                    implode('", "', $keys)
                ));
            }
            $rows[] = new Series(array_values($record));
        }

        return new self($rows, $columns ?? []);
    }

    /**
     * @return array<array<string|int, array<string,mixed>>>
     */
    public function toRecords(): array
    {
        return array_map(function (Series $series) {
            return (array)array_combine($this->columns, $series->toValues());
        }, $this->rows);
    }

    /**
     * @param int|string $index
     */
    public function column($index): Series
    {
        $offset = array_search($index, $this->columns);

        if (!is_int($offset)) {
            throw new ColumnDoesNotExist(sprintf(
                'Could not find column "%s", known columns "%s"',
                $index,
                implode('", "', $this->columns)
            ));
        }

        return new Series(array_map(function (Series $series) use ($offset) {
            return $series->value($offset);
        }, $this->rows));
    }

    /**
     * @param int|string $index
     */
    public function row($index): Row
    {
        if (!isset($this->rows[$index])) {
            throw new RuntimeException(sprintf(
                'Could not find row "%s" in data frame with %s row(s)',
                $index,
                count($this->rows)
            ));
        }

        return new Row((array)array_combine(
            $this->columns,
            $this->rows[$index]->toValues()
        ));
    }

    /**
     * @return Row[]
     */
    public function rows(): array
    {
        return array_map(function (Series $series) {
            return new Row(array_combine($this->columns, $series->toValues()));
        }, $this->rows);
    }

    /**
     * @return scalarOrNull[]
     */
    public function toValues(): array
    {
        return array_reduce($this->rows, function (array $carry, Series $series) {
            return array_merge($carry, $series->toValues());
        }, []);
    }

    /**
     * @return ArrayIterator<Row>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->rows());
    }

    /**
     * @return string[]
     */
    public function columnNames(): array
    {
        return $this->columns;
    }

    /**
     * @return DataFrames<DataFrame>
     */
    public function partition(Closure $partitioner): DataFrames
    {
        return (new Partition())->__invoke($this, $partitioner);
    }

    /**
     * @return array<string, array<scalarOrNull>>
     */
    public function columnValues(): array
    {
        $values = array_combine($this->columns, array_fill(0, count($this->columns), []));

        foreach ($this->columns as $index => $name) {
            foreach ($this->rows as $series) {
                $value = $series->value($index);
                $values[$name][] = $value;
            }
        }

        return $values;
    }

    /**
     * @return array<string, scalar[]>
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
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return in_array($offset, $this->columns);
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->column($offset)->toValues();
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('Not implemented');
    }

    /**
     * @param Closure(Row):bool $closure
     */
    public function filter(Closure $closure): self
    {
        return new self(array_values(array_map(function (Row $row) {
            return $row->toSeries();
        }, array_filter($this->rows(), $closure))), $this->columns);
    }

    public static function empty(): self
    {
        return new self([], []);
    }
}
