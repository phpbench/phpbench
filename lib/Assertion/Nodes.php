<?php

namespace PhpBench\Assertion;

use ArrayIterator;
use IteratorAggregate;
use PhpBench\Assertion\Ast\Node;
use RuntimeException;

/**
 * @implements IteratorAggregate<int, Node>
 */
class Nodes implements IteratorAggregate
{
    /**
     * @var Node[]
     */
    private $nodes;

    /**
     * @param Node[] $nodes
     */
    public function __construct(array $nodes = [])
    {
        $this->nodes = array_values($nodes);
    }

    public function push(Node $node): void
    {
        $this->nodes[] = $node;
    }

    public function pop(): ?Node
    {
        return array_pop($this->nodes);
    }

    /**
     * @return ArrayIterator<int, Node>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->nodes);
    }

    public function singleRemainingNode(): Node
    {
        $node = array_pop($this->nodes);

        if (!$node) {
            throw new RuntimeException(sprintf(
                'Did not parse a single AST node, "%s" nodes remaining',
                count($this->nodes)
            ));
        }

        return $node;
    }

    public function shift(): ?Node
    {
        return array_shift($this->nodes);
    }
}
