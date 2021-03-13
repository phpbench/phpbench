<?php

namespace PhpBench\Expression\ColorMap;

use Closure;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Ast\PercentDifferenceNode;
use PhpBench\Expression\ColorMap;

class Standard8ColorMap implements ColorMap
{
    /**
     * @template T
     *
     * @return array<class-string<T>, string|Closure(T):string>
     */
    public function colors(): array
    {
        /** @phpstan-ignore-next-line */
        return [
            FunctionNode::class => 'fg=green',
            ParenthesisNode::class => 'fg=red',
            PercentDifferenceNode::class => function (Node $node) {
                assert($node instanceof PercentDifferenceNode);

                if (abs($node->percentage()) <= $node->tolerance()) {
                    return 'fg=white';
                }

                return $node->percentage() > 0 ? 'fg=yellow;options=underscore' : 'fg=green;options=underscore';
            },
            DisplayAsNode::class => 'fg=cyan',
            ParameterNode::class => 'fg=white',
            BooleanNode::class => function (Node $node): string {
                assert($node instanceof BooleanNode);

                return $node->value() ? 'fg=blue' : 'fg=red';
            },
            ArithmeticOperatorNode::class => 'fg=yellow',
            ComparisonNode::class => 'fg=yellow',
        ];
    }
}
