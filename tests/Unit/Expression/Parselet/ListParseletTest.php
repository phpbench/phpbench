<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class ListParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield 'single value is not an argument list' => [
            '[12]',
            new ListNode([new IntegerNode(12)])
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield [
            '[12]',
            [],
            '[12]'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function providePrint(): Generator
    {
        yield ['12, 12'];

        yield ['12, 12, 12, 12'];
    }
}
