<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\ArithmeticOperatorNode;
use PhpBench\Expression\Ast\FunctionNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class FunctionParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            'foobar()',
            new FunctionNode('foobar'),
        ];

        yield [
            'foobar(12)',
            new FunctionNode(
                'foobar',
                new ArgumentListNode([new IntegerNode(12)])
            ),
        ];

        yield [
            'foobar(12, 14, 12 + 2)',
            new FunctionNode(
                'foobar',
                new ArgumentListNode([
                    new IntegerNode(12),
                    new IntegerNode(14),
                    new ArithmeticOperatorNode(new IntegerNode(12), '+', new IntegerNode(2))
                ])
            ),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield [
            'mode([12, 12])',
            [],
            '12'
        ];
    }

    public function testEvaluateInvalidArguments(): void
    {
        $this->expectException(EvaluationError::class);
        $this->expectExceptionMessage('mode');
        $this->evaluate($this->parse('mode(12)'));
    }

    /**
     * {@inheritDoc}
     */
    public function providePrint(): Generator
    {
        yield from $this->providePrintFromEvaluate();
    }
}
