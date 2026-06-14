<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\ConcatNode;
use PhpBench\Expression\Ast\LambdaNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class LambdaParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public static function provideParse(): Generator
    {
        yield [
            '(foo, bar) => foo ~ bar',
            new LambdaNode(
                new ArgumentListNode([new VariableNode('foo'), new VariableNode('bar')]),
                new ConcatNode(new VariableNode('foo'), new VariableNode('bar')),
            ),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function provideEvaluate(): Generator
    {
        yield [
            '((first, sec) => first + sec)()',
            [],
            '3'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function providePrint(): Generator
    {
        yield from static::providePrintFromEvaluate();
    }
}
