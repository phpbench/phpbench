<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Serializer;

use PhpBench\Environment\Information;
use PhpBench\Math\Distribution;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Error;
use PhpBench\Model\ErrorStack;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;

class XmlTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
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
            'groups' => [],
            'params' => [],
        ], $params);

        $this->suiteCollection->getSuites()->willReturn([$this->suite->reveal()]);
        $this->suite->getDate()->willReturn(new \DateTime('2015-01-01'));
        $this->suite->getContextName()->willReturn('test');
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
        $this->subject1->getRetryThreshold()->willReturn(10);
        $this->variant1->getWarmup()->willReturn(50);
        $this->variant1->getParameterSet()->willReturn(new ParameterSet(1, $params['params']));
        $this->variant1->hasErrorStack()->willReturn($params['error']);
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

        $this->variant1->getStats()->willReturn(new Distribution([0.1]));
        $this->variant1->getIterator()->willReturn(new \ArrayIterator([
            $this->iteration1->reveal(),
        ]));
        $this->iteration1->getTime()->willReturn(10);
        $this->iteration1->getRevTime()->willReturn(0.1);
        $this->iteration1->getMemory()->willReturn(100);
        $this->iteration1->getZValue()->willReturn(0);
        $this->iteration1->getDeviation()->willReturn(0);
        $this->iteration1->getRejectionCount()->willReturn(5);

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
                    ],
                ],
                <<<'EOT'
<?xml version="1.0"?>
<phpbench version="PHPBENCH_VERSION">
  <suite context="test" date="2015-01-01 00:00:00" config-path="/path/to/config.json">
    <env>
      <info1 foo="bar"/>
    </env>
    <benchmark class="Bench1">
      <subject name="subjectName">
        <group name="group1"/>
        <group name="group2"/>
        <variant sleep="5" output-time-unit="milliseconds" output-time-precision="7" output-mode="throughput" revs="100" warmup="50" retry-threshold="10">
          <parameter name="foo" value="bar"/>
          <parameter name="bar" type="collection">
            <parameter name="baz" value="bon"/>
          </parameter>
          <iteration net-time="10" rev-time="0.1" z-value="0" memory="100" deviation="0" rejection-count="5"/>
          <stats max="0.1" mean="0.1" min="0.1" mode="0.1" rstdev="0" stdev="0" sum="0.1" variance="0"/>
        </variant>
      </subject>
    </benchmark>
  </suite>
</phpbench>

EOT
            ],
            [
                ['error' => true],
                <<<'EOT'
<?xml version="1.0"?>
<phpbench version="PHPBENCH_VERSION">
  <suite context="test" date="2015-01-01 00:00:00" config-path="/path/to/config.json">
    <env>
      <info1 foo="bar"/>
    </env>
    <benchmark class="Bench1">
      <subject name="subjectName">
        <variant sleep="5" output-time-unit="milliseconds" output-time-precision="7" output-mode="throughput" revs="100" warmup="50" retry-threshold="10">
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

        ];
    }
}
