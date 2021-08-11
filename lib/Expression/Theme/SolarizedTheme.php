<?php

namespace PhpBench\Expression\Theme;

use Closure;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\LabelNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Expression\Ast\PercentDifferenceNode;
use PhpBench\Expression\Ast\RelativeDeviationNode;
use PhpBench\Expression\Ast\ToleratedTrue;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\ColorMap;
use PhpBench\Expression\Theme\Util\Color;
use PhpBench\Expression\Theme\Util\Gradient;

/**
 * Colors based on https://github.com/altercation/solarized
 *
 * @implements ColorMap<Node>
 */
class SolarizedTheme implements ColorMap
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
     * @var string
     */
    private $base0;

    /**
     * @var string
     */
    private $base1;

    /**
     * @var string
     */
    private $base2;

    /**
     * @var string
     */
    private $base3;

    /**
     * @var string
     */
    private $neutral;

    public function __construct(bool $light = true)
    {
        if ($light) {
            $this->base0 = self::BASE00;
            $this->base1 = self::BASE01;
            $this->base2 = self::BASE02;
            $this->base3 = self::BASE03;
            $this->neutral = '#222222';

            return;
        }

        $this->neutral = '#aaaaaa';
        $this->base0 = self::BASE0;
        $this->base1 = self::BASE1;
        $this->base2 = self::BASE2;
        $this->base3 = self::BASE3;
    }

    /**
     * @template T
     *
     * @return array<class-string<T>, string|Closure(T):string>
     */
    public function colors(): array
    {

        /** @phpstan-ignore-next-line */
        return [
            LabelNode::class => 'fg='. self::BASE1,
            UnitNode::class => 'fg='. self::BASE1,
            FunctionNode::class => 'fg=' . self::GREEN,
            ParenthesisNode::class => 'fg=' . self::RED,
            PercentDifferenceNode::class => function (Node $node) {
                assert($node instanceof PercentDifferenceNode);

                return 'fg=#' . $this->gradient()->colorAtPercentile(((int)$node->percentage() + 100) / 2)->toHex();
            },
            RelativeDeviationNode::class => function (Node $node) {
                assert($node instanceof RelativeDeviationNode);

                return 'fg=#' . $this->gradient()->colorAtPercentile(
                    50 + (((int)$node->value()))
                )->toHex();
            },
            DisplayAsNode::class => 'fg=default',
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
                Color::fromHex('#aaaaaa'),
                100
            )->to(
                Color::fromHex('#ff0000'),
                100
            );
        }

        return $this->gradient;
    }
}
