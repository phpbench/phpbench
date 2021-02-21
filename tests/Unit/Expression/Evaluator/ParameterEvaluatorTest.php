<?php

namespace PhpBench\Tests\Unit\Expression\Evaluator;

use Generator;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Tests\Unit\Expression\EvaluatorTestCase;

class ParameterEvaluatorTest extends EvaluatorTestCase
{
    /**
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
            ['foo', 'bar'],
            [
                'foo' => $object
            ],
            new IntegerNode(2)
        ];

        yield 'value access' => [
            ['foo'],
            [
                'foo' => 12
            ],
            new IntegerNode(12),
        ];

        yield 'value is array' => [
            ['foo'],
            [
                'foo' => [12, 24]
            ],
            new ListNode([new IntegerNode(12), new IntegerNode(24)]),
        ];

        yield 'value is array 2' => [
            ['foo'],
            [
                'foo' => [12, 24, 37]
            ],
            new ListNode([new IntegerNode(12), new IntegerNode(24), new IntegerNode(37)]),
        ];

        yield 'array access' => [
            ['foo', 'bar'],
            [
                'foo' => [
                    'bar' => 2.1,
                ],
            ],
            new FloatNode(2.1)
        ];

        yield 'nested array access' => [
            ['foo', 'bar', 'baz'],
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
            ['foo', 'bar', 'bar'],
            [
                'foo' => [
                    'bar' => $object,
                ],
            ],
            new IntegerNode(2)
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
            ['one'],
            [
            ],
            'Array does not have key',
        ];

        yield [
            ['one', 'two'],
            [
                'one' => [
                    'three' => 4
                ],
            ],
            'Array does not have key',
        ];

        yield [
            ['one', 'two'],
            [
                'one' => $object
            ],
            'Could not access',
        ];

        yield [
            ['one', 'two', 'three'],
            [
                'one' => [
                    'two' => 12,
                ],
            ],
            'Could not access',
        ];
    }
}
