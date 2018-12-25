<?php

namespace PhpBench\Benchmark\ParamProvider;

use IteratorAggregate;

class SimpleIterator implements IteratorAggregate
{
    public function __construct(array $parameterSets)
    {
    }
}
