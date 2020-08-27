<?php

namespace PhpBench\Tests\Unit\Assertion\Ast;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\Ast\Arguments;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Exception\PropertyAccessError;

class PropertyAccessTest extends TestCase
{
    /**
     * @dataProvider providePropertyAccess
     */
    public function testPropertyAccess(array $segments, array $args, $expected): void
    {
        self::assertEquals($expected, (new PropertyAccess($segments))->resolveValue(new Arguments($args)));
    }

    /**
     * @return Generator<mixed>
     */
    public function providePropertyAccess(): Generator
    {
        $object = new class {
            public function bar(): string {
                return 'hello';
            }
        };

        yield 'object access' => [
            ['foo', 'bar'],
            [
                'foo' => $object
            ],
            'hello'
        ];

        yield 'array access' => [
            ['foo', 'bar'],
            [
                'foo' => [
                    'bar' => 'hello',
                ],
            ],
            'hello'
        ];

        yield 'nested array access' => [
            ['foo', 'bar', 'baz'],
            [
                'foo' => [
                    'bar' => [
                        'baz' => 'goodbye',
                    ]
                ],
            ],
            'goodbye'
        ];

        yield 'array access with nested object' => [
            ['foo', 'bar', 'bar'],
            [
                'foo' => [
                    'bar' => $object,
                ],
            ],
            'hello'
        ];
    }

    /**
     * @dataProvider provideErrors
     */
    public function testErrors(array $segments, array $args, string $expectedMessage): void
    {
        $this->expectException(PropertyAccessError::class);
        $this->expectExceptionMessage($expectedMessage);
        (new PropertyAccess($segments))->resolveValue(new Arguments($args));
    }
    
    /**
     * @return Generator<mixed>
     */
    public function provideErrors(): Generator
    {
        $object = new class {
            public function bar(): string {
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
