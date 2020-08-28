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
            yield 'use right hand side unit when showing left hand side for property access' => [
                new Comparison(
                    new PropertyAccess(['foo', 'bar']),
                    '>',
                    new TimeValue(10, 'seconds'),
                    new TimeValue(5, 'seconds')
                ),
                [
                    'foo' => [
                        'bar' => 10
                    ]
                ],
                '10s > 10s ± 5s',
            ];
        }
}
