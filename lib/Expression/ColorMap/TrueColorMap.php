<?php

namespace PhpBench\Expression\ColorMap;

use Closure;
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
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\ColorMap;
use PhpBench\Expression\ColorMap\Util\Color;
use PhpBench\Expression\ColorMap\Util\Gradient;

/**
 * Colors based on https://github.com/altercation/solarized
 *
 * @implements ColorMap<Node>
 */
class TrueColorMap implements ColorMap
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
     * @var Gradient|null
     */
    private $gradient;

    /**
     * @template T
     *
     * @return array<class-string<T>, string|Closure(T):string>
     */
    public function colors(): array
    {

        /** @phpstan-ignore-next-line */
        return [
            UnitNode::class => 'fg='.self::BASE1,
            FunctionNode::class => 'fg=' . self::GREEN,
            ParenthesisNode::class => 'fg=' . self::RED,
            PercentDifferenceNode::class => function (Node $node) {
                assert($node instanceof PercentDifferenceNode);

                return 'fg=#' . $this->gradient()->colorAtPercentile(((int)$node->percentage() + 100) / 2)->toHex();
            },
            DisplayAsNode::class => 'fg=' . self::BASE2,
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

    private function gradient(): Gradient
    {
        if (!$this->gradient) {
            $this->gradient = Gradient::start(
                Color::fromHex(self::GREEN)
            )->to(
                Color::fromHex(self::BASE2), 100
            )->to(
                Color::fromHex('#ff0000'), 100
            );
        }

        return $this->gradient;
    }
}
