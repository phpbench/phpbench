<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\NodeEvaluator;

/**
 * @template T of Node
 * @implements NodeEvaluator<T>
 */
abstract class AbstractEvaluator implements NodeEvaluator
{
    /**
     * @var class-string<T>
     */
    private $nodeFqn;

    /**
     * @param class-string<T> $nodeFqn
     */
    public function __construct(string $nodeFqn)
    {
        $this->nodeFqn = $nodeFqn;
    }

    public function evaluates(Node $node): bool
    {
        return $node instanceof $this->nodeFqn;
    }
}
