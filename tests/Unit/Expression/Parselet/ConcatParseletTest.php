<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\ConcatNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class ConcatParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public static function provideParse(): Generator
    {
        yield [
            '10 ~ 19',
            new ConcatNode(new IntegerNode(10), new IntegerNode(19)),
        ];

        yield [
            '10 ~ " vs " ~ 19',
            new ConcatNode(
                new IntegerNode(10),
                new ConcatNode(
                    new StringNode(" vs "),
                    new IntegerNode(19)
                )
            ),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function provideEvaluate(): Generator
    {
        yield ['"foo" ~ "bar"', [], 'foobar'];

        yield ['10 ~ "bar"', [], '10bar'];
    }

    /**
     * {@inheritDoc}
     */
    public static function providePrint(): Generator
    {
        yield 'does not show quotes' => ['10 ~ "bar"', [], '10bar'];
    }
}
