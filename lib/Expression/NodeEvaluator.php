<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

/**
 * @template T of Node
 */
interface NodeEvaluator
{
    public function evaluates(Node $node): bool;

    /**
     * @param T $node
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node;
}
