<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainEvaluator;

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
    public function evaluate(MainEvaluator $evaluator, Node $node);
}
