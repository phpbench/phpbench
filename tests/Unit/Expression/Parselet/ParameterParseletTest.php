<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Tests\Unit\Expression\ParseletTestCase;

class ParameterParseletTest extends ParseletTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield 'property' => [
            'foo.bar',
            new ParameterNode(['foo', 'bar'])
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield [
            'foo',
            ['foo' => 12],
            '12'
        ];

        yield [
            'foo.bar',
            ['foo' => ['bar' => 12]],
            '12'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function providePrint(): Generator
    {
        yield from $this->providePrintFromEvaluate();
    }
}
