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

namespace PhpBench\Extensions\XDebug\Tests\Unit;

use PhpBench\Extensions\XDebug\XDebugUtil;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PHPUnit\Framework\TestCase;

class XDebugUtilTest extends TestCase
{
    private $iteration;
    private $subject;
    private $benchmark;
    private $parameters;

    protected function setUp(): void
    {
        $this->iteration = $this->prophesize(Iteration::class);
        $this->subject = $this->prophesize(Subject::class);
        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->parameters = $this->prophesize(ParameterSet::class);
        $this->variant = $this->prophesize(Variant::class);
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
        $this->variant->getParameterSet()->willReturn($this->parameters->reveal());
        $this->subject->getBenchmark()->willReturn($this->benchmark->reveal());
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->iteration->getVariant()->willReturn($this->variant->reveal());
        $result = XDebugUtil::filenameFromIteration($this->iteration->reveal());
        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function provideGenerate()
    {
        return [
            [
                'Benchmark',
                'Subject',
                'Benchmark::Subject.P7',
            ],
            [
                'Benchmark\\Foo',
                'Subject\\//asd',
                'Benchmark_Foo::Subject___asd.P7',
            ],
        ];
    }
}
