<?php

namespace PhpBench\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;

final class DataFrames implements IteratorAggregate, Countable
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

    public function toArray(): array
    {
        return array_map(function (DataFrame $frame) {
            return $frame->toRecords();
        }, $this->dataFrames);
    }

    public function count()
    {
        return count($this->dataFrames);
    }
}
