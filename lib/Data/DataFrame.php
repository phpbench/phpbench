<?php

namespace PhpBench\Data;

use function array_reduce;
use function array_search;
use ArrayIterator;
use IteratorAggregate;
use PhpBench\Data\Func\Partition;
use RuntimeException;

final class DataFrame implements IteratorAggregate
{
    /**
     * @var Series[]
     */
    private $serieses;

    /**
     * @var array
     */
    private $columns;

    public function __construct(array $rows, array $columns)
    {
        $this->serieses = $rows;
        $this->columns = array_values($columns);
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
        }, $this->serieses);
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
        }, $this->serieses));
    }

    /**
     * @param int|string $index
     */
    public function row($index): Row
    {
        if (!isset($this->serieses[$index])) {
            throw new RuntimeException(sprintf(
                'Could not find row "%s" in data frame with %s row(s)',
                $index, count($this->serieses)
            ));
        }

        return new Row(array_combine($this->columns, $this->serieses[$index]->toValues()));
    }

    /**
     * @return Row[]
     */
    public function rows(): array
    {
        return array_map(function (Series $series) {
            return new Row(array_combine($this->columns, $series->toValues()));
        }, $this->serieses);
    }

    public function toValues(): array
    {
        return array_reduce($this->serieses, function (array $carry, Series $series) {
            return array_merge($carry, $series->toValues());
        }, []);
    }

    /**
     * @return ArrayIterator<Series>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->serieses);
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
            foreach ($this->serieses as $row) {
                $values[$name][] = $row->value($index);
            }
        }

        return $values;
    }
}
