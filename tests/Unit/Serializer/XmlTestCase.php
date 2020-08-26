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

namespace PhpBench\Tests\Unit\Serializer;

use PhpBench\Assertion\AssertionFailure;
use PhpBench\Assertion\AssertionFailures;
use PhpBench\Assertion\AssertionWarning;
use PhpBench\Assertion\AssertionWarnings;
use PhpBench\Environment\Information;
use PhpBench\Math\Distribution;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Error;
use PhpBench\Model\ErrorStack;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\ResolvedExecutor;
use PhpBench\Model\Result\ComputedResult;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PHPUnit\Framework\TestCase;

class XmlTestCase extends TestCase
{
    protected function setUp(): void
    {
        $this->suiteCollection = $this->prophesize(SuiteCollection::class);
        $this->suite = $this->prophesize(Suite::class);
        $this->env1 = $this->prophesize(Information::class);
        $this->bench1 = $this->prophesize(Benchmark::class);
        $this->subject1 = $this->prophesize(Subject::class);
        $this->variant1 = $this->prophesize(Variant::class);
        $this->iteration1 = $this->prophesize(Iteration::class);
    }

    public function getSuiteCollection($params)
    {
        $params = array_merge([
            'error' => false,
            'failure' => false,
            'warning' => false,
            'groups' => [],
            'params' => [],
        ], $params);

        $this->suiteCollection->getSuites()->willReturn([$this->suite->reveal()]);
        $this->suite->getUuid()->willReturn(1234);
        $this->suite->getDate()->willReturn(new \DateTime('2015-01-01T00:00:00+00:00'));
        $this->suite->getTag()->willReturn('test');
        $this->suite->getConfigPath()->willReturn('/path/to/config.json');
        $this->suite->getEnvInformations()->willReturn([
            $this->env1,
        ]);
        $this->env1->getName()->willReturn('info1');
        $this->env1->getIterator()->willReturn(new \ArrayIterator([
            'foo' => 'bar',
        ]));
        $this->suite->getBenchmarks()->willReturn([
            $this->bench1->reveal(),
        ]);
        $this->bench1->getSubjects()->willReturn([
            $this->subject1->reveal(),
        ]);
        $this->bench1->getClass()->willReturn('Bench1');
        $this->subject1->getVariants()->willReturn([
            $this->variant1->reveal(),
        ]);
        $this->subject1->getName()->willReturn('subjectName');
        $this->subject1->getGroups()->willReturn($params['groups']);
        $this->subject1->getSleep()->willReturn(5);
        $this->subject1->getOutputTimeUnit()->willReturn('milliseconds');
        $this->subject1->getOutputTimePrecision()->willReturn(7);
        $this->subject1->getOutputMode()->willReturn('throughput');
        $this->subject1->getExecutor()->willReturn(ResolvedExecutor::fromNameAndConfig('foo', new Config('asd', [])));
        $this->subject1->getRetryThreshold()->willReturn(10);
        $this->variant1->getWarmup()->willReturn(50);
        $this->variant1->getBaseline()->willReturn(null);
        $this->variant1->getParameterSet()->willReturn(new ParameterSet('some params', $params['params']));
        $this->variant1->hasErrorStack()->willReturn($params['error']);
        $this->variant1->hasFailed()->willReturn($params['failure']);
        $this->variant1->hasWarning()->willReturn($params['warning']);
        $this->variant1->isComputed()->willReturn(true);
        $this->variant1->getRevolutions()->willReturn(100);

        if ($params['error']) {
            $this->variant1->getErrorStack()->willReturn(
                new ErrorStack(
                    $this->variant1->reveal(),
                    [
                        new Error(
                            'This is an error',
                            'ErrorClass',
                            0, 1, 2,
                            '-- trace --'
                        ),
                    ]
                )
            );
        }

        if ($params['failure']) {
            $this->variant1->getFailures()->willReturn(
                new AssertionFailures($this->variant1->reveal(), [
                    new AssertionFailure('Fail!'),
                ])
            );
        }

        if ($params['warning']) {
            $this->variant1->getWarnings()->willReturn(
                new AssertionWarnings($this->variant1->reveal(), [
                    new AssertionWarning('Warn!'),
                ])
            );
        }

        $this->variant1->getStats()->willReturn(new Distribution([0.1]));
        $this->variant1->getIterator()->willReturn(new \ArrayIterator([
            $this->iteration1->reveal(),
        ]));
        $this->iteration1->getResults()->willReturn([
            new TimeResult(10),
            new MemoryResult(100, 110, 109),
            new ComputedResult(0, 0, 5),
        ]);

        return $this->suiteCollection->reveal();
    }

    public function provideEncode()
    {
        return [
            [
                [
                    'groups' => ['group1', 'group2'],
                    'params' => [
                        'foo' => 'bar',
                        'bar' => [
                            'baz' => 'bon',
                        ],
                        'baz' => null,
                    ],
                ],
                <<<'EOT'
<?xml version="1.0"?>
<phpbench xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="PHPBENCH_VERSION">
  <suite tag="test" context="test" date="2015-01-01T00:00:00+00:00" config-path="/path/to/config.json" uuid="1234">
    <env>
      <info1 foo="bar"/>
    </env>
    <benchmark class="Bench1">
      <subject name="subjectName">
        <executor name="foo"/>
        <group name="group1"/>
        <group name="group2"/>
        <variant sleep="5" output-time-unit="milliseconds" output-time-precision="7" output-mode="throughput" revs="100" warmup="50" retry-threshold="10">
          <parameter-set name="some params">
            <parameter name="foo" value="bar"/>
            <parameter name="bar" type="collection">
              <parameter name="baz" value="bon"/>
            </parameter>
            <parameter name="baz" xsi:nil="true"/>
          </parameter-set>
          <iteration time-net="10" mem-peak="100" mem-real="110" mem-final="109" comp-z-value="0" comp-deviation="0"/>
          <stats max="0.1" mean="0.1" min="0.1" mode="0.1" rstdev="0" stdev="0" sum="0.1" variance="0"/>
        </variant>
      </subject>
    </benchmark>
    <result key="time" class="PhpBench\Model\Result\TimeResult"/>
    <result key="mem" class="PhpBench\Model\Result\MemoryResult"/>
    <result key="comp" class="PhpBench\Model\Result\ComputedResult"/>
  </suite>
</phpbench>

EOT
            ],
            [
                ['error' => true],
                <<<'EOT'
<?xml version="1.0"?>
<phpbench xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="PHPBENCH_VERSION">
  <suite tag="test" context="test" date="2015-01-01T00:00:00+00:00" config-path="/path/to/config.json" uuid="1234">
    <env>
      <info1 foo="bar"/>
    </env>
    <benchmark class="Bench1">
      <subject name="subjectName">
        <executor name="foo"/>
        <variant sleep="5" output-time-unit="milliseconds" output-time-precision="7" output-mode="throughput" revs="100" warmup="50" retry-threshold="10">
          <parameter-set name="some params"/>
          <errors>
            <error exception-class="ErrorClass" code="0" file="1" line="2">This is an error</error>
          </errors>
        </variant>
      </subject>
    </benchmark>
  </suite>
</phpbench>

EOT
            ],
            'failure and warnings' => [
                ['failure' => true, 'warning' => true],
                <<<'EOT'
<?xml version="1.0"?>
<phpbench xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="PHPBENCH_VERSION">
  <suite tag="test" context="test" date="2015-01-01T00:00:00+00:00" config-path="/path/to/config.json" uuid="1234">
    <env>
      <info1 foo="bar"/>
    </env>
    <benchmark class="Bench1">
      <subject name="subjectName">
        <executor name="foo"/>
        <variant sleep="5" output-time-unit="milliseconds" output-time-precision="7" output-mode="throughput" revs="100" warmup="50" retry-threshold="10">
          <parameter-set name="some params"/>
          <warnings>
            <warning>Warn!</warning>
          </warnings>
          <failures>
            <failure>Fail!</failure>
          </failures>
          <iteration time-net="10" mem-peak="100" mem-real="110" mem-final="109" comp-z-value="0" comp-deviation="0"/>
          <stats max="0.1" mean="0.1" min="0.1" mode="0.1" rstdev="0" stdev="0" sum="0.1" variance="0"/>
        </variant>
      </subject>
    </benchmark>
    <result key="time" class="PhpBench\Model\Result\TimeResult"/>
    <result key="mem" class="PhpBench\Model\Result\MemoryResult"/>
    <result key="comp" class="PhpBench\Model\Result\ComputedResult"/>
  </suite>
</phpbench>

EOT
            ],
        ];
    }
}
