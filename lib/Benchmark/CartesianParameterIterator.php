<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Benchmark;

use PatchRanger\CartesianIterator;
use PhpBench\Model\ParameterSet;

class CartesianParameterIterator extends CartesianIterator
{
    private $index = 0;

    public function __construct(array $parameterSets)
    {
        parent::__construct(static::MIT_KEYS_ASSOC);
        /** @var ParameterSet $parameterSet */
        foreach ($parameterSets as $parameterSet) {
            $key = 0;
            $values = [];
            foreach ($parameterSet as $array) {
                foreach ($array as $key => $value) {
                    $values[] = $value;
                }
            }
            $this->attachIterator(new \ArrayIterator($values), $key);
        }
    }

    public function current()
    {
        return $this->valid()
            ? new ParameterSet($this->index, parent::current())
            : null;
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        parent::next();
        $this->index++;
        return $this->current();
    }

    public function rewind()
    {
        parent::rewind();
        $this->index = 0;
        return $this->current();
    }
}
