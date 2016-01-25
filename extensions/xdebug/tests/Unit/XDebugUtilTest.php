<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\XDebug\Tests\Unit;

use PhpBench\Extensions\XDebug\XDebugUtil;

class XDebugUtilTest extends \PHPUnit_Framework_TestCase
{
    private $iteration;
    private $subject;
    private $benchmark;
    private $parameters;

    public function setUp()
    {
        $this->iteration = $this->prophesize('PhpBench\Model\Iteration');
        $this->subject = $this->prophesize('PhpBench\Model\Subject');
        $this->benchmark = $this->prophesize('PhpBench\Model\Benchmark');
        $this->parameters = $this->prophesize('PhpBench\Model\ParameterSet');
    }

    /**
     * It should generate a filename for an iteration.
     *
     * @dataProvider provideGenerate
     */
    public function testGenerate($class, $subject, $expected)
    {
        $this->benchmark->getClass()->willReturn($class);
        $this->subject->getName()->willReturn($subject);

        $this->parameters->getIndex()->willReturn(7);
        $this->iteration->getParameters()->willReturn($this->parameters->reveal());
        $this->subject->getBenchmark()->willReturn($this->benchmark->reveal());
        $this->iteration->getSubject()->willReturn($this->subject->reveal());
        $result = XDebugUtil::filenameFromIteration($this->iteration->reveal());
        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function provideGenerate()
    {
        return array(
            array(
                'Benchmark',
                'Subject',
                'Benchmark::Subject.P7.cachegrind',
            ),
            array(
                'Benchmark\\Foo',
                'Subject\\//asd',
                'Benchmark_Foo::Subject___asd.P7.cachegrind',
            ),
        );
    }
}
