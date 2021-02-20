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
     *
     * @return mixed
     */
    public function evaluate(Evaluator $evaluator, Node $node): Node;
}
