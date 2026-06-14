<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\LambdaNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\Exception\SyntaxError;
use PhpBench\Expression\NodeEvaluator;

final class LambdaEvaluator implements NodeEvaluator
{
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof LambdaNode) {
            return null;
        }

        $names = array_map(function (VariableNode $param) {
            return $param->name();
        }, $node->params()->nodes());

        if (count($names) !== count($params)) {
            throw new EvaluationError($node, sprintf('Expected %d arguments, got %d',
                count($names),
                count($params)
            ));
        }

        $args = array_combine($names, $params);

        return $evaluator->evaluate($node->expression(), $args);
    }
}
