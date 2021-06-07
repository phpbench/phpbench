<?php

namespace PhpBench\Tests\Unit\Expression\Parselet;

use Generator;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\NullSafeNode;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\VariableNode;
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
            new ParameterNode([
                new VariableNode('foo'),
                new VariableNode('bar')
            ])
        ];

        yield 'field 1' => [
            'foo["bar"]',
            new ParameterNode([
                new VariableNode('foo'),
                new StringNode('bar')
            ])
        ];

        yield 'field 2' => [
            'foo["bar"]["bar"]',
            new ParameterNode([
                new VariableNode('foo'),
                new StringNode('bar'),
                new StringNode('bar'),
            ])
        ];

        yield 'field and property' => [
            'foo["bar"]["bar"].baz',
            new ParameterNode([
                new VariableNode('foo'),
                new StringNode('bar'),
                new StringNode('bar'),
                new VariableNode('baz')
            ])
        ];

        yield 'expression' => [
            'foo[bar <= 10]["bar"].baz',
            new ParameterNode([
                new VariableNode('foo'),
                new ComparisonNode(
                    new ParameterNode([new VariableNode('bar')]),
                    '<=',
                    new IntegerNode(10)
                ),
                new StringNode('bar'),
                new VariableNode('baz')
            ])
        ];

        yield 'null safe property' => [
            'foo?.bar',
            new ParameterNode([
                new VariableNode('foo'),
                new NullSafeNode(new VariableNode('bar'))
            ])
        ];

        yield 'null safe field 2' => [
            'foo["bar"]?["bar"]',
            new ParameterNode([
                new VariableNode('foo'),
                new StringNode('bar'),
                new NullSafeNode(new StringNode('bar')),
            ])
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideEvaluate(): Generator
    {
        yield [
            'foo.bar',
            ['foo' => ['bar' => 12]],
            '12'
        ];

        yield [
            'foo.bar',
            ['foo' => ['bar' => 'foo']],
            'foo'
        ];

        yield [
            'foo.bar',
            ['foo' => ['bar' => null]],
            'null'
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
