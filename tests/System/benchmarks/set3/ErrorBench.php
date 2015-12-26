<?php

namespace PhpBench\Tests\System\benchmarks\set3;

/**
 * This benchmark throws exceptions.
 */
class ErrorBench
{
    public function benchNothing()
    {
    }

    public function benchException()
    {
        throw new \Exception('Arff');
    }

    public function benchNothingElse()
    {
    }
}
