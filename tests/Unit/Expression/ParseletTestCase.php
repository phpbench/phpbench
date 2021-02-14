<?php

namespace PhpBench\Tests\Unit\Expression;

use Generator;
use PhpBench\Expression\Ast\Node;
use PhpBench\Assertion\ExpressionEvaluator;
use PhpBench\Assertion\ExpressionLexer;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Parser;
use PhpBench\Expression\ParserFactory;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\TestCase;

abstract class ParseletTestCase extends ParserTestCase
{
    /**
     * @dataProvider provideParse
     */
    public function testParse(string $expr, Node $expected): void
    {
        self::assertEquals($expected, $this->parse($expr));
    }

    /**
     * @return mixed
     * @param mixed[] $params
     */
    public function evaluate(Node $node, array $params = [])
    {
        return $this->container()->get(
            Evaluator::class
        )->evaluate($node);
    }

    /**
     * @dataProvider provideEvaluate
     * @param mixed[] $params
     * @param mixed $expectedValue
     */
    public function testEvaluate(string $expr, array $params, $expectedValue): void
    {
        self::assertEquals($expectedValue, $this->evaluate($this->parse($expr), $params));
    }

    /**
     * @return Generator<mixed>
     */
    abstract public function provideParse(): Generator;

    /**
     * @return Generator<mixed>
     */
    abstract public function provideEvaluate(): Generator;
}

