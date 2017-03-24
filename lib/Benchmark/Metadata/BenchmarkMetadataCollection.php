<?php

namespace PhpBench\Benchmark\Metadata;

class BenchmarkMetadataCollection implements \IteratorAggregate
{
    private $benchmarkMetadatas;

    public function __construct(array $benchmarkMetadatas)
    {
        $this->benchmarkMetadatas = $benchmarkMetadatas;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->benchmarkMetadatas);
    }
}
