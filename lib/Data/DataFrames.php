<?php

namespace PhpBench\Data;

use ArrayIterator;
use IteratorAggregate;
use RuntimeException;

class DataFrames implements IteratorAggregate
{
    /**
     * @var DataFrame[]
     */
    private $dataFrames;

    public function __construct(array $dataFrames)
    {
        $this->dataFrames = $dataFrames;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->dataFrames);
    }

    public function combine(): DataFrame
    {
        $rows = [];
        $columns = null;
        foreach ($this->dataFrames as $dataFrame) {
            if ($columns !== null && $columns !== $dataFrame->columnNames()) {
                throw new RuntimeException(sprintf(
                    'DataFrame with columns "%s" does not match initial data frame with columns "%s"',
                    implode('", "', $dataFrame->columnNames()), implode('", "', $columns)
                ));
            }

            if (null === $columns) {
                $columns = $dataFrame->columnNames();
            }

            foreach ($dataFrame as $row) {
                $rows[] = $row;
            }
        }

        return new DataFrame($rows, $columns);
    }

    public function toArray(): array
    {
        return array_map(function (DataFrame $frame) {
            return $frame->toRecords();
        }, $this->dataFrames);
    }
}
