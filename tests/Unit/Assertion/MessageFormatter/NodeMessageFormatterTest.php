<?php

namespace PhpBench\Tests\Unit\Assertion\MessageFormatter;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\MessageFormatter;
use PhpBench\Assertion\MessageFormatter\NodeMessageFormatter;

class NodeMessageFormatterTest extends TestCase
{
        /**
         * @dataProvider provideTimeValue
         * @dataProvider provideComparison
         */
        public function testFormat(Node $node, array $args, string $expected): void
        {
            self::assertEquals($expected, (new NodeMessageFormatter($args))->format($node));
        }
        
        /**
         * @return Generator<mixed>
         */
        public function provideTimeValue(): Generator
        {
            yield [
                new TimeValue(10, 'microseconds'),
                [],
                '10μs',
            ];

            yield [
                new TimeValue(10, 'milliseconds'),
                [],
                '10ms',
            ];

            yield [
                new TimeValue(10, 'seconds'),
                [],
                '10s',
            ];
        }

        /**
         * @return Generator<mixed>
         */
        public function provideComparison(): Generator
        {
            yield 'normalise property access unit 1' => [
                new Comparison(
                    new PropertyAccess(['foo', 'bar']),
                    '>',
                    new TimeValue(10, 'seconds'),
                    new TimeValue(5, 'seconds')
                ),
                [
                    'foo' => [
                        'bar' => 1E7
                    ]
                ],
                '10s > 10s ± 5s',
            ];

            yield 'normalise property access unit 2' => [
                new Comparison(
                    new TimeValue(10, 'seconds'),
                    '>',
                    new PropertyAccess(['foo', 'bar']),
                    new TimeValue(5, 'seconds')
                ),
                [
                    'foo' => [
                        'bar' => 1E7
                    ]
                ],
                '10s > 10s ± 5s',
            ];

            yield 'normalise property access unit 3' => [
                new Comparison(
                    new TimeValue(10, 'seconds'),
                    '>',
                    new TimeValue(5, 'seconds'),
                    new PropertyAccess(['foo', 'bar'])
                ),
                [
                    'foo' => [
                        'bar' => 5E6
                    ]
                ],
                '10s > 5s ± 5s',
            ];
        }
}
