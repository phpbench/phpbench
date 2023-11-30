<?php

namespace PhpBench\Data;

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<DataFrame>
 */
final class DataFrames implements IteratorAggregate
{
    public function __construct(
        /**
         * @var DataFrame[]
         */
        private readonly array $dataFrames
    )
    {
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
