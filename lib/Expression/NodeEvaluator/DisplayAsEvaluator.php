<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodeEvaluator;

class DisplayAsEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof DisplayAsNode) {
            return null;
        }

        $value = $evaluator->evaluateType($node->node(), NumberNode::class, $params);
        $unit = new UnitNode($evaluator->evaluateType($node->as()->unit(), StringNode::class, $params));

        $precision = $node->precision();

        if ($precision) {
            $precision = $evaluator->evaluateType($node->precision(), PhpValue::class, $params);
        }

        return new DisplayAsNode($value, $unit, $precision);
    }
}
