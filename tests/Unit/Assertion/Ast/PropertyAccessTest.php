<?php

namespace PhpBench\Tests\Unit\Assertion\Ast;

use Generator;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Exception\PropertyAccessError;
use PhpBench\Tests\Unit\Assertion\ExpressionParserTestCase;

class PropertyAccessTest extends ExpressionParserTestCase
{
    /**
     * @dataProvider providePropertyAccess
     */
    public function testPropertyAccess(array $segments, array $args, $expected): void
    {
        self::assertEquals($expected, $this->evaluate(new PropertyAccess($segments), $args));
    }

    /**
     * @return Generator<mixed>
     */
    public function providePropertyAccess(): Generator
    {
        $object = new class {
            public function bar()
            {
                return 2;
            }
        };

        yield 'object access' => [
            ['foo', 'bar'],
            [
                'foo' => $object
            ],
            2
        ];

        return;

        yield 'array access' => [
            ['foo', 'bar'],
            [
                'foo' => [
                    'bar' => 2.1,
                ],
            ],
            2.1
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
            4.5
        ];

        yield 'array access with nested object' => [
            ['foo', 'bar', 'bar'],
            [
                'foo' => [
                    'bar' => $object,
                ],
            ],
            2
        ];
    }

    /**
     * @dataProvider provideErrors
     */
    public function testErrors(array $segments, array $args, string $expectedMessage): void
    {
        $this->expectException(PropertyAccessError::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->evaluate(new PropertyAccess($segments), $args);
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
    }
}
