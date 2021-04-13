<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

interface NodeEvaluator
{
    /**
     * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node;
}
