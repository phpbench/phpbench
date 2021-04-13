<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class BooleanParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            'true',
            new BooleanNode(true),
        ];

        yield [
            'false',
            new BooleanNode(false),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield ['true', [], 'true'];

        yield ['false', [], 'false'];
    }

    /**
     * {@inheritDoc}
     */
    public function providePrint(): Generator
    {
        yield from $this->providePrintFromEvaluate();
    }
}
