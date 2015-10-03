<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Benchmark;

use PhpBench\Benchmark\Iteration;
use PhpBench\Benchmark\Runner;
use PhpBench\PhpBench;

class RunnerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->collectionBuilder = $this->prophesize('PhpBench\Benchmark\CollectionBuilder');
        $this->collection = $this->prophesize('PhpBench\Benchmark\Collection');
        $this->subject = $this->prophesize('PhpBench\Benchmark\Metadata\SubjectMetadata');
        $this->collectionBuilder->buildCollection(__DIR__, array(), array())->willReturn($this->collection);
        $this->executor = $this->prophesize('PhpBench\Benchmark\Executor');
        $this->benchmark = $this->prophesize('PhpBench\Benchmark\Metadata\BenchmarkMetadata');

        $this->runner = new Runner(
            $this->collectionBuilder->reveal(),
            $this->executor->reveal(),
            null
        );
    }

    /**
     * It should run the tests.
     *
     * - With 1 iteration, 1 revolution
     * - With 1 iteration, 4 revolutions
     *
     * @dataProvider provideRunner
     */
    public function testRunner($iterations, $revs, array $parameters, $expected, $exception = null)
    {
        if ($exception) {
            $this->setExpectedException($exception[0], $exception[1]);
        }
        $this->collection->getBenchmarks()->willReturn(array(
            $this->benchmark,
        ));
        $this->subject->getIterations()->willReturn($iterations);
        $this->subject->getName()->willReturn('benchFoo');
        $this->subject->getBeforeMethods()->willReturn(array('beforeFoo'));
        $this->subject->getAfterMethods()->willReturn(array());
        $this->subject->getParameterSets()->willReturn(array(array($parameters)));
        $this->subject->getGroups()->willReturn(array());
        $this->subject->getRevs()->willReturn($revs);
        $this->benchmark->getSubjectMetadatas()->willReturn(array(
            $this->subject->reveal(),
        ));
        $this->benchmark->getClass()->willReturn('Benchmark');

        if (!$exception) {
            foreach ($revs as $revCount) {
                $this->executor->execute($this->subject->reveal(), $revCount, $parameters)->shouldBeCalledTimes($iterations);
            }
        }

        $result = $this->runner->runAll(__DIR__);

        $this->assertInstanceOf('PhpBench\Benchmark\SuiteDocument', $result);
        $this->assertEquals(
            trim(sprintf(<<<EOT
<?xml version="1.0"?>
<phpbench version="%s">
%s
</phpbench>
EOT
            , PhpBench::VERSION, $expected)),
            trim($result->saveXml())
        );
    }

    public function provideRunner()
    {
        return array(
            array(
                1,
                array(1),
                array(),
                <<<EOT
  <benchmark class="Benchmark">
    <subject name="benchFoo">
      <variant>
        <iteration revs="1" time="" memory=""/>
      </variant>
    </subject>
  </benchmark>
EOT
            ),
            array(
                1,
                array(1, 3),
                array(),
                <<<EOT
  <benchmark class="Benchmark">
    <subject name="benchFoo">
      <variant>
        <iteration revs="1" time="" memory=""/>
        <iteration revs="3" time="" memory=""/>
      </variant>
    </subject>
  </benchmark>
EOT
            ),
            array(
                4,
                array(1, 3),
                array(),
                <<<EOT
  <benchmark class="Benchmark">
    <subject name="benchFoo">
      <variant>
        <iteration revs="1" time="" memory=""/>
        <iteration revs="3" time="" memory=""/>
        <iteration revs="1" time="" memory=""/>
        <iteration revs="3" time="" memory=""/>
        <iteration revs="1" time="" memory=""/>
        <iteration revs="3" time="" memory=""/>
        <iteration revs="1" time="" memory=""/>
        <iteration revs="3" time="" memory=""/>
      </variant>
    </subject>
  </benchmark>
EOT
            ),
            array(
                1,
                array(1),
                array('one' => 'two', 'three' => 'four'),
                <<<EOT
  <benchmark class="Benchmark">
    <subject name="benchFoo">
      <variant>
        <parameter name="one" value="two"/>
        <parameter name="three" value="four"/>
        <iteration revs="1" time="" memory=""/>
      </variant>
    </subject>
  </benchmark>
EOT
            ),
            array(
                1,
                array(1),
                array('one', 'two'),
                <<<EOT
  <benchmark class="Benchmark">
    <subject name="benchFoo">
      <variant>
        <parameter name="0" value="one"/>
        <parameter name="1" value="two"/>
        <iteration revs="1" time="" memory=""/>
      </variant>
    </subject>
  </benchmark>
EOT
            ),
            array(
                1,
                array(1),
                array('one' => array('three' => 'four')),
                <<<EOT
  <benchmark class="Benchmark">
    <subject name="benchFoo">
      <variant>
        <parameter name="one" type="collection">
          <parameter name="three" value="four"/>
        </parameter>
        <iteration revs="1" time="" memory=""/>
      </variant>
    </subject>
  </benchmark>
EOT
            ),
            array(
                1,
                array(1),
                array('one' => array('three' => new \stdClass())),
                '',
                array('InvalidArgumentException', 'Parameters must be either scalars or arrays, got: stdClass'),
            ),
        );
    }
}

class RunnerTestBenchCase
{
}
