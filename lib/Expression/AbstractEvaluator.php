<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

/**
 * @template T of Node
 */
abstract class AbstractEvaluator
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

    /**
     * @param T $node
     * @return mixed
     */
    abstract public function evaluate(Evaluator $evaluator, Node $node);
}
