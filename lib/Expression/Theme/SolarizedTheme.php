<?php

namespace PhpBench\Expression\Theme;

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
    private const BASE1 = '#93a1a1';
    private const YELLOW = '#b58900';
    private const ORANGE = '#cb4b16';
    private const RED = '#dc322f';
    private const GREEN = '#859900';
    private const BLUE = '#268bd2';

    /**
     * @var Gradient|null
     */
    private $gradient;

    public function __construct()
    {
    }

    public function colors(): array
    {
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
