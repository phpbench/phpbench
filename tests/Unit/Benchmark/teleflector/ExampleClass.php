<?php

namespace PhpBench\Tests\Unit\Benchmark\teleflector;

use PhpBench\BenchmarkInterface;

/**
 * Some doc comment
 */
class ExampleClass implements BenchmarkInterface
{
    /**
     * Method One Comment
     */
    public function methodOne()
    {
    }

    /**
     * Method Two Comment
     */
    public function methodTwo()
    {
    }

    public function provideParamsOne()
    {
        return array(
            array(
                'one' => 'two',
                'three' => 'four',
            ),
        );
    }

    public function provideParamsTwo()
    {
        return array(
            array(
                'five' => 'six',
                'seven' => 'eight',
            ),
        );
    }
}
