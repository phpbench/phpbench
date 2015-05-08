<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

class CartesianParamIterator implements \Iterator
{
    private $sets;
    private $index = 0;
    private $max;
    private $current;
    private $break = false;

    public function __construct(array $parameterSets)
    {
        foreach ($parameterSets as $parameterSet) {
            $this->sets[] = new \ArrayIterator($parameterSet);
        }

        $this->max = count($parameterSets) - 1;
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {
        for ($index = 0; $index <= $this->max; ++$index) {
            $this->sets[$index]->next();

            if (true === $this->sets[$index]->valid()) {
                break;
            }

            $this->sets[$index]->rewind();

            if ($index === $this->max) {
                $this->break = true;
                break;
            }
        }

        $this->index++;
        $this->update();

        return $this->current;
    }

    public function key()
    {
        return $this->index;
    }

    public function rewind()
    {
        $this->index = 0;
        foreach ($this->sets as $set) {
            $set->rewind();
        }
        $this->update();

        return $this->current();
    }

    public function valid()
    {
        return false === $this->break;
    }

    private function update()
    {
        $this->current = array();
        foreach ($this->sets as $set) {
            $this->current = array_merge($this->current, $set->current());
        }
    }
}
