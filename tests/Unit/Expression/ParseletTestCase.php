<?php

namespace PhpBench\Tests\Unit\Expression;

use Generator;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\MainEvaluator;
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
     * @return mixed
     *
     * @param mixed[] $params
     */
    public function evaluate(Node $node, array $params = [])
    {
        return $this->container()->get(
            MainEvaluator::class
        )->evaluate($node);
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
     * @param mixed $expectedValue
     */
    public function testEvaluate(string $expr, array $params, $expectedValue): void
    {
        self::assertEquals($expectedValue, $this->evaluate($this->parse($expr), $params));
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
     * @param mixed $expectedValue
     */
    public function testPrint(string $expr, array $params = [], string $expected = null): void
    {
        $result = $this->print($this->parse($expr));
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
