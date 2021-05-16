<?php

namespace PhpBench\Tests\Unit\Expression\NodeEvaluator;

use Generator;
use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\ComparisonNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Tests\Unit\Expression\EvaluatorTestCase;

class ParameterEvaluatorTest extends EvaluatorTestCase
{
    /**
     * @dataProvider provideDataFrame
     * @dataProvider providePropertyAccess
     *
     * @param string[] $segments
     * @param parameters $params
     */
    public function testPropertyAccess(array $segments, array $params, Node $expected): void
    {
        self::assertEquals($expected, $this->evaluateNode(
            new ParameterNode($segments),
            $params
        ));
    }

    /**
     * @return Generator<mixed>
     */
    public function providePropertyAccess(): Generator
    {
        $object = new class {
            public function bar(): int
            {
                return 2;
            }
        };

        yield 'object access' => [
            [new VariableNode('foo'), new VariableNode('bar')],
            [
                'foo' => $object
            ],
            new IntegerNode(2)
        ];

        yield 'value access' => [
            [new VariableNode('foo')],
            [
                'foo' => 12
            ],
            new IntegerNode(12),
        ];

        yield 'value is array' => [
            [new VariableNode('foo')],
            [
                'foo' => [12, 24]
            ],
            new ListNode([new IntegerNode(12), new IntegerNode(24)]),
        ];

        yield 'value is array 2' => [
            [new VariableNode('foo')],
            [
                'foo' => [12, 24, 37]
            ],
            new ListNode([new IntegerNode(12), new IntegerNode(24), new IntegerNode(37)]),
        ];

        yield 'array access' => [
            [new VariableNode('foo'), new VariableNode('bar')],
            [
                'foo' => [
                    'bar' => 2.1,
                ],
            ],
            new FloatNode(2.1)
        ];

        yield 'nested array access' => [
            [
                new VariableNode('foo'),
                new VariableNode('bar'),
                new VariableNode('baz')
            ],
            [
                'foo' => [
                    'bar' => [
                        'baz' => 4.5,
                    ]
                ],
            ],
            new FloatNode(4.5)
        ];

        yield 'array access with nested object' => [
            [
                new VariableNode('foo'),
                new VariableNode('bar'),
                new VariableNode('bar')
            ],
            [
                'foo' => [
                    'bar' => $object,
                ],
            ],
            new IntegerNode(2)
        ];
    }

    public function provideDataFrame(): Generator
    {
        $frame = DataFrame::fromRowSeries([
            [ 'patch', 10, 'rabbit' ],
            [ 'henry', 5, 'pidgeon' ],
            [ 'sahra', 5, 'fox' ],
            [ 'boxer', 7, 'dog' ],
        ], ['name', 'age', 'animal']);

        yield 'access column' => [
            [
                new VariableNode('foo'),
                new StringNode('age'),
            ],
            [
                'foo' => $frame
            ],
            PhpValueFactory::fromValue([10, 5, 5, 7])
        ];

        yield '5 year olds' => [
            [
                new VariableNode('foo'),
                new ComparisonNode(
                    new ParameterNode([new VariableNode('age')]),
                    '=',
                    new IntegerNode(5)
                ),
                new VariableNode('name'),
                new IntegerNode(0),
            ],
            [
                'foo' => $frame
            ],
            new StringNode('henry')
        ];

        yield 'access dataframe' => [
            [
                new VariableNode('foo'),
            ],
            [
                'foo' => $frame
            ],
            new ListNode([
                new ListNode([
                    new StringNode('patch'),
                    new IntegerNode(10),
                    new StringNode('rabbit')
                ]),
                new ListNode([
                    new StringNode('henry'),
                    new IntegerNode(5),
                    new StringNode('pidgeon')
                ]),
                new ListNode([
                    new StringNode('sahra'),
                    new IntegerNode(5),
                    new StringNode('fox')
                ]),
                new ListNode([
                    new StringNode('boxer'),
                    new IntegerNode(7),
                    new StringNode('dog')
                ]),
            ])
        ];
    }

    /**
     * @dataProvider provideErrors
     */
    public function testErrors(array $segments, array $params, string $expectedMessage): void
    {
        $this->expectException(EvaluationError::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->evaluateNode(new ParameterNode($segments), $params);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideErrors(): Generator
    {
        $object = new class {
            public function bar(): string
            {
                return 'hello';
            }
        };

        yield [
            [new VariableNode('one')],
            [
            ],
            'Array does not have key',
        ];

        yield [
            [new VariableNode('one'), new VariableNode('two')],
            [
                'one' => [
                    'three' => 4
                ],
            ],
            'Array does not have key',
        ];

        yield [
            [new VariableNode('one'), new VariableNode('two')],
            [
                'one' => $object
            ],
            'Could not access',
        ];

        yield [
            [new VariableNode('one'), new VariableNode('two'), new VariableNode('three')],
            [
                'one' => [
                    'two' => 12,
                ],
            ],
            'Could not access',
        ];

        yield 'invalid expression' => [
            [
                new VariableNode('foo'),
                new ComparisonNode(
                    new ParameterNode([new VariableNode('age')]),
                    '=',
                    new IntegerNode(5)
                ),
                new IntegerNode(0),
                new VariableNode('name'),
            ],
            [
                'foo' => []
            ],
            'Expression provided',
        ];
    }
}
