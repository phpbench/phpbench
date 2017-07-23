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

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Constraint\Comparison;
use PhpBench\Expression\Constraint\Composite;
use PhpBench\Expression\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    private $parser;

    public function setUp()
    {
        $this->parser = new Parser();
    }

    /**
     * It should throw an exception if the JSON is invalid.
     *
     * @expectedException Seld\JsonLint\ParsingException
     */
    public function testInvalidJson()
    {
        $this->parser->parse('{"$eq": ["benchmark", foo__');
    }

    /**
     * It should parse simple constraints.
     *
     * @dataProvider provideComparison
     */
    public function testComparison($operator)
    {
        $string = sprintf('{benchmark: {%s: "foo"}}', $operator);
        $constraint = $this->parser->parse($string);

        $this->assertEquals(
            new Comparison($operator, 'benchmark', 'foo'),
            $constraint
        );
    }

    public function provideComparison()
    {
        return [
            [
                '$gt',
            ],
            [
                '$lt',
            ],
            [
                '$eq',
            ],
            [
                '$neq',
            ],
            [
                '$gte',
            ],
            [
                '$lte',
            ],
            [
                '$in',
            ],
            [
                '$nin',
            ],
        ];
    }

    /**
     * It should throw an exception if an invalid operator is provided.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessge Unknown operator
     */
    public function testInvalidOperator()
    {
        $this->parser->parse('{foo: {$asdasd: "bar"}}');
    }

    /**
     * It should allow composite constraints.
     */
    public function testCompositeConstraints()
    {
        $composite = $this->parser->parse('
$and: [
    {
        $or: [
            { benchmark: { $eq: "foo" } }, 
            { subject: { $eq: "bar" } }
        ]
    },
    {
        subject.revs: { $eq: 5000 }
    }
]
');

        $this->assertEquals(
            new Composite(
                '$and',
                new Composite(
                    '$or',
                    new Comparison('$eq', 'benchmark', 'foo'),
                    new Comparison('$eq', 'subject', 'bar')
                ),
                new Comparison('$eq', 'subject.revs', 5000)
            ),
            $composite
        );
    }

    /**
     * It should allow key: value equality expressions.
     */
    public function testKeyValueEquality()
    {
        $comparison = $this->parser->parse(
            'benchmark: "Foobar"'
        );

        $this->assertEquals(
            new Comparison('$eq', 'benchmark', 'Foobar'),
            $comparison
        );
    }

    /**
     * It should allow multiple arguments to a composite.
     */
    public function testMultipleComposite()
    {
        $composite = $this->parser->parse('
            $and: [
                { benchmark: "foobar" },
                { number: { $gt: 2015 } },
                { name: "daniel" }
            ]
        ');

        $this->assertEquals(
            new Composite(
                '$and',
                new Composite(
                    '$and',
                    new Comparison('$eq', 'benchmark', 'foobar'),
                    new Comparison('$gt', 'number', 2015)
                ),
                new Comparison('$eq', 'name', 'daniel')
            ),
            $composite
        );

        $composite = $this->parser->parse('
            $and: [
                { benchmark: "foobar" },
                { number: { $gt: 2015 } },
                { name: "daniel" },
                { barbar: "booboo" }
            ]
            ');

        $this->assertEquals(
            new Composite(
                '$and',
                new Composite(
                    '$and',
                    new Composite(
                        '$and',
                        new Comparison('$eq', 'benchmark', 'foobar'),
                        new Comparison('$gt', 'number', 2015)
                    ),
                    new Comparison('$eq', 'name', 'daniel')
                ),
                new Comparison('$eq', 'barbar', 'booboo')
            ),
            $composite
        );
    }

    /**
     * It should allow implicit and.
     */
    public function testImplicitAnd()
    {
        $constraint = $this->parser->parse('benchmark: "foobar", subject: "barfoo", name: "daniel"');
        $this->assertEquals(
            new Composite(
                '$and',
                new Composite(
                    '$and',
                    new Comparison('$eq', 'benchmark', 'foobar'),
                    new Comparison('$eq', 'subject', 'barfoo')
                ),
                new Comparison('$eq', 'name', 'daniel')
            ),
            $constraint
        );
    }
}
