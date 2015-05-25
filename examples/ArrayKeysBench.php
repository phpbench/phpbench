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

/**
 */
class ArrayKeysBench implements Benchmark
{
    private $array;
    private $values;

    public function init()
    {
        $this->array = array_fill(0, 50000, 'this is a test');
        $this->values = array_combine(array_keys($this->array), array_keys($this->array));
    }

    /**
     * @description array_key_exists
     * @beforeMethod init
     * @revs 100000
     * @revs 10000
     * @revs 1000
     * @revs 100
     * @revs 10
     */
    public function benchArrayKeyExists($iteration, $revolution)
    {
        array_key_exists($revolution, $this->array);
    }

    /**
     * @description isset
     * @beforeMethod init
     * @revs 10
     * @revs 100
     * @revs 1000
     * @revs 10000
     * @revs 100000
     */
    public function benchIsset($iteration, $revolution)
    {
        isset($this->array[$revolution]);
    }

    /**
     * @description in_array
     * @beforeMethod init
     * @revs 10000
     * @revs 1000
     * @revs 100
     * @revs 10
     */
    public function benchInArray($iteration, $revolution)
    {
        in_array($revolution, $this->values);
    }
}
