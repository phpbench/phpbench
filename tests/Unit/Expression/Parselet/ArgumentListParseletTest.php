<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class ArgumentListParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield 'single value is not an argument list' => [
            '12',
            new IntegerNode(12)
        ];

        yield 'two comma separated values are an argument list' => [
            '12,24',
            new ArgumentListNode(
                new IntegerNode(12),
                new IntegerNode(24)
            )
        ];

        yield 'multiple values' => [
            '12, 12, 12',
            new ArgumentListNode(
                new IntegerNode(12),
                new ArgumentListNode(
                    new IntegerNode(12),
                    new IntegerNode(12)
                )
            )
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield [
            '12, 12',
[],
            [12, 12]
        ];
    }
}
