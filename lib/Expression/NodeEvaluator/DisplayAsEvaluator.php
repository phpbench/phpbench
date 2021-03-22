<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\Evaluator;

/**
 * @extends AbstractEvaluator<DisplayAsNode>
 */
class DisplayAsEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(DisplayAsNode::class);
    }

    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): Node
    {
        $value = $evaluator->evaluateType($node->node(), NumberNode::class, $params);
        $unit = new UnitNode($evaluator->evaluateType($node->as()->unit(), StringNode::class, $params));

        $precision = $node->precision();

        if ($precision) {
            $precision = $evaluator->evaluateType($node->precision(), PhpValue::class, $params);
        }

        return new DisplayAsNode($value, $unit, $precision);
    }
}
