<?php

namespace PhpBench\Expression\ColorMap;

use Closure;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Ast\PercentDifferenceNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\ToleratedTrue;
use PhpBench\Expression\ColorMap;
use PhpBench\Expression\ColorMap\Util\Color;
use PhpBench\Expression\ColorMap\Util\Gradient;

/**
 * @implements ColorMap<Node>
 */
class SolarisedColorMap implements ColorMap
{
    private const BASE03 = '#002b36';
    private const BASE02 = '#073642';
    private const BASE01 = '#586e75';
    private const BASE00 = '#657b83';
    private const BASE0 = '#839496';
    private const BASE1 = '#93a1a1';
    private const BASE2 = '#eee8d5';
    private const BASE3 = '#fdf6e3';
    private const YELLOW = '#b58900';
    private const ORANGE = '#cb4b16';
    private const RED = '#dc322f';
    private const MAGENTA = '#d33682';
    private const VIOLET = '#6c71c4';
    private const BLUE = '#268bd2';
    private const CYAN = '#2aa198';
    private const GREEN = '#859900';

    /**
     * @template T
     *
     * @return array<class-string<T>, string|Closure(T):string>
     */
    public function colors(): array
    {

        /** @phpstan-ignore-next-line */
        return [
            FunctionNode::class => 'fg=' . self::GREEN,
            ParenthesisNode::class => 'fg=' . self::RED,
            PercentDifferenceNode::class => function (Node $node) {
                assert($node instanceof PercentDifferenceNode);

                $gradient = Gradient::start(
                    Color::fromHex(self::GREEN)
                )->to(
                    Color::fromHex(self::BASE2), 100
                )->to(
                    Color::fromHex(self::RED), 100
                )->toArray();
                
                $percent = (int)$node->percentage();
                $value = $percent < 0 ? max(-100, $percent) : min(100, $percent);
                $color = $gradient[$value + 100];

                return 'fg=#' . $color->toHex();
            },
            DisplayAsNode::class => 'fg=' . self::BASE2,
            StringNode::class => 'fg=' . self::BASE2,
            ParameterNode::class => 'fg='  .self::ORANGE,
            BooleanNode::class => function (Node $node): string {
                assert($node instanceof BooleanNode);

                return $node->value() ? 'fg=' . self::BLUE : 'fg=' . self::RED;
            },
            ToleratedTrue::class => 'fg=' . self::BLUE,
            ArithmeticOperatorNode::class => 'fg=' . self::YELLOW,
            ComparisonNode::class => 'fg=' . self::YELLOW,
        ];
    }
}
