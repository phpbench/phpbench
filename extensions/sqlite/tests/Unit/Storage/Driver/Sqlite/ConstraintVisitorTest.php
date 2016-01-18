<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Storage\Driver\Sqlite;

use PhpBench\Expression\Parser;
use PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite\ConstraintVisitor;

class ConstraintVisitorTest extends \PHPUnit_Framework_TestCase
{
    private $visitor;

    public function setUp()
    {
        $this->visitor = new ConstraintVisitor();
        $this->parser = new Parser();
    }

    /**
     * It should translate a JSON query into an SQL query.
     *
     * @dataProvider provideVisit
     */
    public function testVisit($jsonString, $expectedSql, $expectedValues)
    {
        $constraint = $this->parser->parse($jsonString);
        $query = $this->visitor->visit($constraint);
        $sql = substr($query[0], strpos($query[0], 'WHERE') + 6);
        $this->assertEquals($expectedSql, $sql);
        $this->assertEquals($expectedValues, $query[1]);
    }

    public function provideVisit()
    {
        return array(
            array(
                'param[nb_foobars]: 5', 'parameter.key = :param0 AND parameter.value = :param1', array('param0' => 'nb_foobars', 'param1' => 5),
            ),
            array(
                '$or: [ { benchmark: "foobar" }, { subject: "benchFoo" }]',
                '(subject.benchmark = :param0 OR subject.name = :param1)',
                array(
                    'param0' => 'foobar',
                    'param1' => 'benchFoo',
                ),
            ),
            array(
                '$or: [ { benchmark: "foobar", date: "2015-10-10" }, { subject: "benchFoo" }]',
                '((subject.benchmark = :param0 AND run.date = :param1) OR subject.name = :param2)',
                array(
                    'param0' => 'foobar',
                    'param1' => '2015-10-10',
                    'param2' => 'benchFoo',
                ),
            ),
            array(
                'benchmark: "foobar"',
                'subject.benchmark = :param0',
                array(
                    'param0' => 'foobar',
                ),
            ),
            array(
                'revs: 1000',
                'subject.revolutions = :param0',
                array(
                    'param0' => 1000,
                ),
            ),
            array(
                'date: { $gt: "2016-01-31" }',
                'run.date > :param0',
                array(
                    'param0' => '2016-01-31',
                ),
            ),

            array(
                'run: { $eq: 1 }', 'run.id = :param0', array('param0' => '1'),
            ),
            array(
                'run: { $neq: 1 }', 'run.id != :param0', array('param0' => '1'),
            ),
            array(
                'run: { $gt: 1 }', 'run.id > :param0', array('param0' => '1'),
            ),
            array(
                'run: { $lt: 1 }', 'run.id < :param0', array('param0' => '1'),
            ),
            array(
                'run: { $gte: 1 }', 'run.id >= :param0', array('param0' => '1'),
            ),
            array(
                'run: { $lte: 1 }', 'run.id <= :param0', array('param0' => '1'),
            ),
            array(
                'run: { $in: [1, 2]}', 'run.id IN (:param0, :param1)', array(
                    'param0' => '1',
                    'param1' => '2',
                ),
            ),
            array(
                'benchmark: { $regex: "hello" }', 'subject.benchmark REGEXP :param0', array('param0' => 'hello'),
            ),
        );
    }

    /**
     * It should throw an exception if an unknown composite operator is supplied.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown composite operator
     */
    public function testInvalidCompositeOperator()
    {
        $composite = $this->prophesize('PhpBench\Expression\Constraint\Composite');
        $composite->getOperator()->willReturn('$boobar');

        $this->visitor->visit($composite->reveal());
    }

    /**
     * It should add a join for groups when needed.
     */
    public function testGroupJoin()
    {
        $constraint = $this->prophesize('PhpBench\Expression\Constraint\Comparison');
        $constraint->getComparator()->willReturn('$eq');
        $constraint->getField()->willReturn('group');
        $constraint->getValue()->willReturn('one');

        $sql = $this->visitor->visit($constraint->reveal());
        $this->assertRegExp('{LEFT JOIN sgroup}', $sql[0]);
        $this->assertRegExp('{sgroup.name = :param0}', $sql[0]);
    }

    /**
     * It should add joins for parameters when required.
     */
    public function testParamJoin()
    {
        $constraint = $this->prophesize('PhpBench\Expression\Constraint\Comparison');
        $constraint->getComparator()->willReturn('$eq');
        $constraint->getField()->willReturn('param[foo]');
        $constraint->getValue()->willReturn('one');

        $sql = $this->visitor->visit($constraint->reveal());
        $this->assertRegExp('{LEFT JOIN variant_parameter}', $sql[0]);
        $this->assertRegExp('{LEFT JOIN parameter}', $sql[0]);
    }

    /**
     * It should throw an exception if an invalid field is specified.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown field "boobar", allowed fields:
     */
    public function testUnknownField()
    {
        $constraint = $this->prophesize('PhpBench\Expression\Constraint\Comparison');
        $constraint->getComparator()->willReturn('$eq');
        $constraint->getField()->willReturn('boobar');

        $this->visitor->visit($constraint->reveal());
    }

    /**
     * It should throw an exception if an unknown comparator is encountered.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unsupported comparator
     */
    public function testUnknownComparator()
    {
        $constraint = $this->prophesize('PhpBench\Expression\Constraint\Comparison');
        $constraint->getComparator()->willReturn('narf');
        $constraint->getField()->willReturn('subject');

        $this->visitor->visit($constraint->reveal());
    }

    /**
     * It should throw an exception if an unsupported constraint class is given.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unsupported constraint class
     */
    public function testUnsupportedConstraintClass()
    {
        $constraint = $this->prophesize('PhpBench\Expression\Constraint\Constraint');
        $this->visitor->visit($constraint->reveal());
    }

    /**
     * It should throw an exception if a non-array value is passed as an argument to $in.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage array
     */
    public function testNonArrayIn()
    {
        $constraint = $this->prophesize('PhpBench\Expression\Constraint\Comparison');
        $constraint->getComparator()->willReturn('$in');
        $constraint->getValue()->willReturn('hei');
        $constraint->getField()->willReturn('benchmark');

        $this->visitor->visit($constraint->reveal());
    }
}
