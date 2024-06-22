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
     * @param DataFrame[] $dataFrames
     */
    public function __construct(
        private readonly array $dataFrames
    ) {
    }

    /**
     * @return ArrayIterator<array-key, DataFrame>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->dataFrames);
    }

    /**
     * @return array<array-key, array<array<string, mixed>>>
     */
    public function toArray(): array
    {
        return array_map(static function (DataFrame $frame) {
            return $frame->toRecords();
        }, $this->dataFrames);
    }
}
