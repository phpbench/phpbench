<?php

namespace PhpBench\Assertion;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpBench\Assertion\Ast\Node;
use RuntimeException;

/**
 * @implements IteratorAggregate<int, Node>
 */
class Nodes implements IteratorAggregate, Countable
{
    /**
     * @var Node[]
     */
    private $nodes;

    public function __construct()
    {
        $this->nodes = [];
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
     * @template T of Node
     *
     * @param class-string<T> $nodeType
     *
     * @return T|null
     */
    public function popType(string $nodeType): ?Node
    {
        $node = $this->pop();

        if (null === $node) {
            return null;
        }

        if (!$node instanceof $nodeType) {
            return null;
        }

        return $node;
    }

    public function shift(): ?Node
    {
        return array_shift($this->nodes);
    }

    /**
     * @template T of Node
     *
     * @param class-string<T> $nodeType
     *
     * @return T|null
     */
    public function shiftType(string $nodeType): ?Node
    {
        $node = $this->shift();

        if (null === $node) {
            return null;
        }

        if (!$node instanceof $nodeType) {
            return null;
        }

        return $node;
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

        if (count($this->nodes)) {
            throw new RuntimeException(sprintf(
                'Did not parse a single AST node, "%s" nodes remaining',
                count($this->nodes)
            ));
        }

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->nodes);
    }
}
