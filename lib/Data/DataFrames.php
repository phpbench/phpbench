<?php

namespace PhpBench\Data;

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<DataFrame>
 */
final class DataFrames implements IteratorAggregate
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
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->dataFrames);
    }

    public function toArray(): array
    {
        return array_map(function (DataFrame $frame) {
            return $frame->toRecords();
        }, $this->dataFrames);
    }
}
