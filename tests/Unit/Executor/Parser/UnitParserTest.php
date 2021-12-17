<?php

namespace PhpBench\Tests\Unit\Executor\Parser;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Executor\Parser\Ast\UnitNode;
use PhpBench\Executor\Parser\UnitParser;

class UnitParserTest extends TestCase
{
    /**
     * @dataProvider provideParse
     */
    public function testParse(array $program, UnitNode $expected)
    {
        $parser = new UnitParser();
        self::assertEquals($expected, $parser->parse($program));
    }

    public function provideParse(): Generator
    {
        yield [
            [
            ],
            new UnitNode('root', [
            ]),
        ];
        yield [
            [
                'foobar',
            ],
            new UnitNode('root', [
                new UnitNode('foobar'),
            ]),
        ];

        yield [
            [
                ['baz'],
            ],
            new UnitNode('root', [
                new UnitNode('baz'),
            ]),
        ];

        yield [
            [
                ['baz', ['boo']],
            ],
            new UnitNode('root', [
                new UnitNode('baz', [
                    new UnitNode('boo'),
                ]),
            ]),
        ];

        yield [
            [
                'foobar',
                [
                    'baz',
                ],
                [
                    'boo',
                ],
            ],
            new UnitNode('root', [
                new UnitNode('foobar', [
                    new UnitNode('baz', []),
                    new UnitNode('boo', []),
                ]),
            ]),
        ];

        yield [
            [
                'one',
                [
                    'baz',
                    'boo',
                    'bam',
                    [
                        'bop'
                    ]
                ],
                'two',
                'three',
                [
                    'four',
                ]
            ],
            new UnitNode('root', [
                new UnitNode('one', [
                    new UnitNode('baz'),
                    new UnitNode('boo'),
                    new UnitNode('bam', [
                        new UnitNode('bop'),
                    ])
                ]),
                new UnitNode('two'),
                new UnitNode('three', [
                    new UnitNode('four'),
                ]),
            ]),
        ];
    }
}
