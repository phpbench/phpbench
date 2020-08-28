<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Unit\Assertion;

use Generator;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\WithinRangeOf;

class ExpressionParserTest extends ExpressionParserTestCase
{
    /**
     * @dataProvider provideWithin
     * @dataProvider provideComparison
     */
    public function testParse(string $dsl, Node $expected): void
    {
        $this->assertEquals($expected, $this->parse($dsl));
    }

    public function provideWithin(): Generator
    {
        yield 'within' => [
            'this.mean within 10% of baseline.mean',
            new WithinRangeOf(
                new PropertyAccess(['this', 'mean']),
                new PercentageValue(10),
                new PropertyAccess(['baseline', 'mean']),
                new TimeValue(0, 'microseconds')
            )
        ];

        yield [
            'this.mean WITHIN 10% OF baseline.mean',
            new WithinRangeOf(
                new PropertyAccess(['this', 'mean']),
                new PercentageValue(10),
                new PropertyAccess(['baseline', 'mean']),
                new TimeValue(0, 'microseconds')
            )
        ];

        yield [
            'this.mem_peak < 100 microseconds',
            new Comparison(
                new PropertyAccess(['this', 'mem_peak']),
                '<',
                new TimeValue(100, 'microseconds'),
                new TimeValue(0, 'microseconds')
            )
        ];
    }
    
    /**
     * @return Generator<mixed>
     */
    public function provideComparison(): Generator
    {
        yield [
            'this.mem_peak < 100 microseconds',
            new Comparison(
                new PropertyAccess(['this', 'mem_peak']),
                '<',
                new TimeValue(100, 'microseconds'),
                new TimeValue(0, 'microseconds')
            )
        ];

        yield [
            'this.mem_peak < 100 microseconds +/- 50 microseconds',
            new Comparison(
                new PropertyAccess(['this', 'mem_peak']),
                '<',
                new TimeValue(100, 'microseconds'),
                new TimeValue(50, 'microseconds'),
                new TimeValue(0, 'microseconds')
            )
        ];
    }
}
