<?php

use PhpBench\BenchCase;
use PhpBench\BenchIteration;

class ParserCase implements BenchCase
{
    /**
     * @beforeMethod beforeSelectSql
     * @paramProvider provideNodes
     * @paramProvider provideColumns
     * @iterations 3
     * @description Run a select query
     */
    public function benchSelectSql(BenchIteration $iteration)
    {
    }

    /**
     * @beforeMethod setupSelectSql
     * @paramProvider provideNodes
     * @paramProvider provideColumns
     * @iterations 3
     * @description Run a select query
     */
    public function benchTraverseSomething(BenchIteration $iteration)
    {
    }
}
