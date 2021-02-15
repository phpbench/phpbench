<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ParenthesisNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class ParenthesisParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            '(1)',
            new ParenthesisNode(new IntegerNode(1)),
        ];

        yield [
            '(1 + 1)',
            new ParenthesisNode(
                new BinaryOperatorNode(
                    new IntegerNode(1),
                    '+',
                    new IntegerNode(1),
                )
            ),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield ['(1 + 2) * 3', [], 9];

        yield ['1 + 2 * 3', [], 7];

        yield ['1 + (2 * 3)', [], 7];
    }
}
