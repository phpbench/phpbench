<?php

namespace PhpBench\Expression\Theme;

use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Ast\PercentDifferenceNode;
use PhpBench\Expression\Ast\ToleratedTrue;
use PhpBench\Expression\ColorMap;

/**
 * @implements ColorMap<Node>
 */
class EightColorTheme implements ColorMap
{
    public function colors(): array
    {
        return [
            FunctionNode::class => 'fg=green',
            ParenthesisNode::class => 'fg=red',
            PercentDifferenceNode::class => function (Node $node) {
                assert($node instanceof PercentDifferenceNode);

                return $node->percentage() > 0 ? 'fg=red' : 'fg=green';
            },
            DisplayAsNode::class => 'fg=cyan',
            ParameterNode::class => 'fg=white',
            BooleanNode::class => function (Node $node): string {
                assert($node instanceof BooleanNode);

                return $node->value() ? 'fg=blue' : 'fg=red';
            },
            ToleratedTrue::class => 'fg=blue',
            ArithmeticOperatorNode::class => 'fg=yellow',
            ComparisonNode::class => 'fg=yellow',
        ];
    }
}
