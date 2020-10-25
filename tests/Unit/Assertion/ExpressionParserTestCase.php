<?php

namespace PhpBench\Tests\Unit\Assertion;

use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\ExpressionEvaluator;
use PhpBench\Assertion\ExpressionParser;
use PhpBench\Assertion\MessageFormatter;
use PhpBench\Tests\TestCase;
use Prophecy\Argument;

class ExpressionParserTestCase extends TestCase
{
    protected function parse(string $expression): Node
    {
        return (new ExpressionParser([]))->parse($expression);
    }

    /**
     */
    protected function evaluateExpression(string $expression, array $args)
    {
        $formatter = $this->prophesize(MessageFormatter::class);
        $formatter->format(Argument::type(Node::class))->willReturn('');

        return (new ExpressionEvaluator($formatter->reveal(), $args))->evaluate($this->parse($expression));
    }

    /**
     */
    protected function evaluate(Node $node, array $args = [])
    {
        $formatter = $this->prophesize(MessageFormatter::class);
        $formatter->format(Argument::type(Node::class))->willReturn('');

        return (new ExpressionEvaluator($formatter->reveal(), $args))->evaluate($node);
    }
}
