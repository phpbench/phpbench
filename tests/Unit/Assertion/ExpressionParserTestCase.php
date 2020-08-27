<?php

namespace PhpBench\Tests\Unit\Assertion;

use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\ExpressionEvaluator;
use PhpBench\Assertion\ExpressionParser;

class ExpressionParserTestCase extends TestCase
{
    protected function parse(string $expression): Node
    {
        return (new ExpressionParser())->parse($expression);
    }

    /**
     * @return mixed
     */
    protected function evaluateExpression(string $expression, array $args)
    {
        return (new ExpressionEvaluator($args))->evaluate($this->parse($expression));
    }

    /**
     * @return mixed
     */
    protected function evaluate(Node $node, array $args)
    {
        return (new ExpressionEvaluator($args))->evaluate($node);
    }
}
