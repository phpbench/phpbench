<?php

namespace PhpBench\Tests\Unit\Executor\Parser;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Executor\Parser\Ast\StageNode;
use PhpBench\Executor\Parser\StageLexer;
use PhpBench\Executor\Parser\StageParser;
use PhpBench\Expression\Tokens;

class StageParserTest extends TestCase
{
    /**
     * @dataProvider provideParse
     */
    public function testParse(string $expression, StageNode $expected): void
    {
        $tokens = (new StageLexer())->lex($expression);
        self::assertEquals($expected, (new StageParser())->parse($tokens));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideParse(): Generator
    {
        yield [
            '',
            new StageNode('root', [
            ])
        ];
        yield [
            'stage',
            new StageNode('root', [
                new StageNode('stage'),
            ])
        ];

        yield [
            'stage;two;',
            new StageNode('root', [
                new StageNode('stage'),
                new StageNode('two'),
            ])
        ];

        yield [
            'stage;two;three',
            new StageNode('root', [
                new StageNode('stage'),
                new StageNode('two'),
                new StageNode('three'),
            ])
        ];
 
        yield [
            'stage;two{three}',
            new StageNode('root', [
                new StageNode('stage'),
                new StageNode('two', [
                    new StageNode('three'),
                ])
            ])
        ];

        yield [
            'warmup;hrtime{memory{revsIteration{beforeMethods;callSubject;afterMethods}}}',
            new StageNode('root', [
                new StageNode('warmup'),
                new StageNode('hrtime', [
                    new StageNode('memory', [
                        new StageNode('revsIteration', [
                            new StageNode('beforeMethods'),
                            new StageNode('callSubject'),
                            new StageNode('afterMethods'),
                        ])
                    ]),
                ])
            ]),
        ];
    }
}
