<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
