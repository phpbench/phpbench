<?php

namespace PhpBench\Tests\Unit\Expression\Printer;

use Generator;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Tests\Unit\Expression\ParserTestCase;

class EvaluatingPrinterTest extends ParserTestCase
{
    /**
     * @dataProvider provideEvaluate
     */
    public function testEvaluate(string $expr, array $params, string $expected): void
    {
        $printer = new EvaluatingPrinter(
            $this->container()->get(NodePrinter::class),
            $this->container()->get(Evaluator::class),
            [
                FunctionNode::class
            ]
        );

        self::assertEquals(
            $expected,
            $printer->print($this->parse(
                $expr
            ), [])
        );
    }

    public function provideEvaluate(): Generator
    {
        yield [
            '2 > 10',
            [],
            '2 > 10',
        ];

        yield [
            'mean([1, 2, 3])',
            [],
            '2',
        ];

        yield [
            'mean([1, 2, 3]) / 2',
            [],
            '2 / 2',
        ];

        yield [
            'mode([1, 2, 3])',
            [],
            '1.998043052838',
        ];
    }
}
