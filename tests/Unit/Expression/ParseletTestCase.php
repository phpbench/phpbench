<?php

namespace PhpBench\Tests\Unit\Expression;

use Generator;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Printer;

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
     * @param parameters $params
     */
    public function evaluate(Node $node, array $params = []): Node
    {
        return $this->container()->get(
            Evaluator::class
        )->evaluate($node, $params);
    }

    /**
     * @param parameters $params
     */
    public function print(Node $node, array $params = []): string
    {
        return $this->container()->get(Printer::class)->print($node, $params);
    }

    /**
     * @dataProvider provideEvaluate
     *
     * @param parameters $params
     */
    public function testEvaluate(string $expr, array $params, string $expected): void
    {
        self::assertEquals(
            $expected,
            $this->print($this->evaluate($this->parse($expr), $params))
        );
    }

    protected function providePrintFromEvaluate(): Generator
    {
        foreach ($this->provideEvaluate() as [$expr, $params]) {
            yield [$expr, $params];
        }
    }

    /**
     * @dataProvider providePrint
     *
     * @param parameters $params
     */
    public function testPrint(string $expr, array $params = [], string $expected = null): void
    {
        $result = $this->print($this->parse($expr), $params);
        self::assertEquals($expected ?: $expr, $result);
    }

    /**
     * @return Generator<mixed>
     */
    abstract public function provideParse(): Generator;

    /**
     * @return Generator<mixed>
     */
    abstract public function provideEvaluate(): Generator;

    /**
     * @return Generator<mixed>
     */
    abstract public function providePrint(): Generator;
}
