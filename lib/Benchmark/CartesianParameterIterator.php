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

use ArrayIterator;
use Iterator;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\ParameterSetsCollection;

/**
 * @implements Iterator<ParameterSet>
 */
class CartesianParameterIterator implements Iterator
{
    /**
     * @var array<int,ArrayIterator<string, ParameterSet>>
     */
    private $sets = [];

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var int
     */
    private $max;

    /**
     * @var array<mixed>
     */
    private $current = [];

    /**
     * @var bool
     */
    private $break = false;

    /**
     * @var string
     */
    private $key;

    public function __construct(ParameterSetsCollection $parameterSetsCollection)
    {
        foreach ($parameterSetsCollection as $parameterSets) {
            $this->sets[] = $parameterSets->getIterator();
        }

        if (0 === $parameterSetsCollection->count()) {
            $this->break = true;
        }

        $this->max = count($parameterSetsCollection) - 1;
    }

    public function current(): ParameterSet
    {
        return $this->getParameterSet();
    }

    public function next(): void
    {
        for ($index = 0; $index <= $this->max; $index++) {
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
    }

    public function key(): string
    {
        return $this->key;
    }

    public function rewind(): void
    {
        $this->index = 0;

        foreach ($this->sets as $set) {
            $set->rewind();
        }
        $this->update();
    }

    public function valid(): bool
    {
        return false === $this->break;
    }

    private function update(): void
    {
        $this->current = [];
        $key = [];

        foreach ($this->sets as $set) {
            $current = $set->current();
            /** @phpstan-ignore-next-line */
            $this->current = array_merge($this->current, $current ? $current->toArray() : []);
            $key[] = $set->key();
        }
        $this->key = implode(',', $key);
    }

    private function getParameterSet(): ParameterSet
    {
        return ParameterSet::fromParameterContainers($this->key, $this->current);
    }
}
