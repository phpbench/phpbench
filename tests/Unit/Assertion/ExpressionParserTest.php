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

use PHPUnit\Framework\TestCase;
use Generator;
use PhpBench\Assertion\Ast\Comparator;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\Condition;
use PhpBench\Assertion\Ast\Microseconds;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\Unit;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\Variable;
use PhpBench\Assertion\Ast\WithinRangeOf;
use PhpBench\Assertion\ExpressionParser;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit as SebastianBergmannUnit;

class ExpressionParserTest extends ExpressionParserTestCase
{
    /**
     * @dataProvider provideGeneral
     */
    public function testParse(string $dsl, Node $expected): void
    {
        $this->assertEquals($expected, $this->parse($dsl));
    }
    
    /**
     * @return Generator<mixed>
     */
    public function provideGeneral(): Generator
    {
        yield 'within' => [
            'this.mean within 10% of baseline.mean',
            new WithinRangeOf(
                new PropertyAccess(['this', 'mean']),
                new PercentageValue(10),
                new PropertyAccess(['baseline', 'mean']),
            )
        ];

        yield [
            'this.mean WITHIN 10% OF baseline.mean',
            new WithinRangeOf(
                new PropertyAccess(['this', 'mean']),
                new PercentageValue(10),
                new PropertyAccess(['baseline', 'mean']),
            )
        ];

        yield [
            'this.mem_peak less than 100 microseconds',
            new Comparison(
                new PropertyAccess(['this', 'mem_peak']),
                'less than',
                new TimeValue(100, 'microseconds'),
            )
        ];
    }
}
