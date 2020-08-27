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
use PhpBench\Assertion\AssertionData;
use PhpBench\Assertion\Ast\Arguments;
use PhpBench\Assertion\Ast\Comparator;
use PhpBench\Assertion\Ast\Condition;
use PhpBench\Assertion\Ast\Microseconds;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\Unit;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\Variable;
use PhpBench\Assertion\Ast\Within;
use PhpBench\Assertion\Parser;
use PhpBench\Math\Distribution;
use SebastianBergmann\CodeCoverage\Report\Xml\Unit as SebastianBergmannUnit;

class ConditionTest extends TestCase
{
    /**
     * @dataProvider provideGeneral
     */
    public function testCondition(string $condition, array $arguments, bool $shouldBeSatisfied): void
    {
        $result = (new Parser())->parse($condition)->isSatisfied(new Arguments($arguments));
        $this->assertEquals($shouldBeSatisfied, $result);
    }
    
    /**
     * @return Generator<mixed>
     */
    public function provideGeneral(): Generator
    {
        yield 'within 1' => [
            'this.getMean within 10% of baseline.getMean',
            [
                'this' => new Distribution([10, 10, 10]),
                'baseline' => new Distribution([10, 10, 10])
            ],
            true
        ];

        yield 'within 2' => [
            'this.getMean less than 10 microseconds',
            [
                'this' => new Distribution([9, 9, 9]),
            ],
            false
        ];

        yield 'within 3' => [
            'this.getMean within 70% of baseline.getMean',
            [
                'this' => new Distribution([30, 30, 30]),
                'baseline' => new Distribution([10, 10, 10])
            ],
            true
        ];
    }
}

