<?php

use PhpBench\BenchCase;
use PhpBench\BenchIteration;

class ParserCaseInvalidAnnotation implements BenchCase
{
    /**
     * @inasdld beforeSelectSql
     */
    public function benchSelectSql(BenchIteration $iteration)
    {
    }
}
