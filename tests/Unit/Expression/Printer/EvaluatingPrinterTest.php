<?php

namespace PhpBench\Tests\Unit\Expression\Printer;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\NodePrinters;
use PhpBench\Expression\Printer\EvaluatingPrinter;
use PhpBench\Tests\Unit\Expression\ParserTestCase;

class EvaluatingPrinterTest extends ParserTestCase
{
    /**
     * @dataProvider provideEvaluate
     */
    public function testEvaluate(string $expr, array $params, string $expected)
    {
        $printer = new EvaluatingPrinter(
            $this->container()->get(NodePrinters::class),
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
            '       2       ',
        ];

        yield [
            'mode([1, 2, 3])',
            [],
            '1.9980430528375',
        ];
    }
}
