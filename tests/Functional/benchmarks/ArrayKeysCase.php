<?php

namespace PhpBench\Tests\Functional\benchmarks;

use PhpBench\Benchmark;

class ArrayKeysCase implements Benchmark
{
    private $array;

    public function provide()
    {
        $this->array = array('one' => 'one', 'two' => 'two', 'three' => 'three');
    }

    /**
     * @description isset
     * @beforeMethod provide
     * @iterations 1000
     */
    public function benchIsset()
    {
        array_key_exists('two', $this->array);
    }

    /**
     * @description in_array
     * @beforeMethod provide
     * @iterations 1000
     */
    public function benchInArray()
    {
        array_key_exists('two', $this->array);
    }

    /**
     * @description array_key_exists
     * @beforeMethod provide
     * @iterations 1000
     */
    public function benchArrayKeyExists()
    {
        array_key_exists('two', $this->array);
    }
}
