<?php

namespace PhpBench\Assertion;

use Countable;
use IteratorAggregate;
use PhpBench\Assertion\Scope;

class Scope implements IteratorAggregate, Countable
{
    /**
     * @var Nodes[]
     */
    private $stack;

    /**
     * @var BufferStack|null
     */
    private $parent;

    public function __construct(?Scope $parent = null)
    {
        $this->nodes = new Nodes();
        $this->parent = $parent;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return $this->nodes;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
    }
}
