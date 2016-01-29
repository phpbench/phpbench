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

use PhpBench\Math\Distribution;
use PhpBench\Model\Error;
use PhpBench\Model\ErrorStack;
use PhpBench\Model\ParameterSet;

class XmlTestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->suiteCollection = $this->prophesize('PhpBench\Model\SuiteCollection');
        $this->suite = $this->prophesize('PhpBench\Model\Suite');
        $this->env1 = $this->prophesize('PhpBench\Environment\Information');
        $this->bench1 = $this->prophesize('PhpBench\Model\Benchmark');
        $this->subject1 = $this->prophesize('PhpBench\Model\Subject');
        $this->variant1 = $this->prophesize('PhpBench\Model\Variant');
        $this->iteration1 = $this->prophesize('PhpBench\Model\Iteration');
    }

    public function getSuiteCollection($params)
    {
        $params = array_merge(array(
            'error' => false,
            'groups' => array(),
            'params' => array(),
        ), $params);

        $this->suiteCollection->getSuites()->willReturn(array($this->suite->reveal()));
        $this->suite->getDate()->willReturn(new \DateTime('2015-01-01'));
        $this->suite->getContextName()->willReturn('test');
        $this->suite->getConfigPath()->willReturn('/path/to/config.json');
        $this->suite->getEnvInformations()->willReturn(array(
            $this->env1,
        ));
        $this->env1->getName()->willReturn('info1');
        $this->env1->getIterator()->willReturn(new \ArrayIterator(array(
            'foo' => 'bar',
        )));
        $this->suite->getBenchmarks()->willReturn(array(
            $this->bench1->reveal(),
        ));
        $this->bench1->getSubjects()->willReturn(array(
            $this->subject1->reveal(),
        ));
        $this->bench1->getClass()->willReturn('Bench1');
        $this->subject1->getVariants()->willReturn(array(
            $this->variant1->reveal(),
        ));
        $this->subject1->getName()->willReturn('subjectName');
        $this->subject1->getGroups()->willReturn($params['groups']);
        $this->subject1->getSleep()->willReturn(5);
        $this->subject1->getOutputTimeUnit()->willReturn('milliseconds');
        $this->subject1->getOutputMode()->willReturn('throughput');
        $this->subject1->getRevs()->willReturn(100);
        $this->subject1->getRetryThreshold()->willReturn(10);
        $this->subject1->getWarmup()->willReturn(50);
        $this->variant1->getParameterSet()->willReturn(new ParameterSet(1, $params['params']));
        $this->variant1->hasErrorStack()->willReturn($params['error']);
        $this->variant1->isComputed()->willReturn(true);

        if ($params['error']) {
            $this->variant1->getErrorStack()->willReturn(
                new ErrorStack(
                    $this->variant1->reveal(),
                    array(
                        new Error(
                            'This is an error',
                            'ErrorClass',
                            0, 1, 2,
                            '-- trace --'
                        ),
                    )
                )
            );
        }

        $this->variant1->getStats()->willReturn(new Distribution(array(0.1)));
        $this->variant1->getIterator()->willReturn(new \ArrayIterator(array(
            $this->iteration1->reveal(),
        )));
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
        return array(
            array(
                array(
                    'groups' => array('group1', 'group2'),
                    'params' => array(
                        'foo' => 'bar',
                        'bar' => array(
                            'baz' => 'bon',
                        ),
                    ),
                ),
                <<<'EOT'
<?xml version="1.0"?>
<phpbench version="0.10.0-dev">
  <suite context="test" date="2015-01-01 00:00:00" config-path="/path/to/config.json">
    <env>
      <info1 foo="bar"/>
    </env>
    <benchmark class="Bench1">
      <subject name="subjectName">
        <group name="group1"/>
        <group name="group2"/>
        <variant sleep="5" output-time-unit="milliseconds" output-mode="throughput" revs="100" warmup="50" retry-threshold="10">
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
            ),
            array(
                array('error' => true),
                <<<'EOT'
<?xml version="1.0"?>
<phpbench version="0.10.0-dev">
  <suite context="test" date="2015-01-01 00:00:00" config-path="/path/to/config.json">
    <env>
      <info1 foo="bar"/>
    </env>
    <benchmark class="Bench1">
      <subject name="subjectName">
        <variant sleep="5" output-time-unit="milliseconds" output-mode="throughput" revs="100" warmup="50" retry-threshold="10">
          <errors>
            <error exception-class="ErrorClass" code="0" file="1" line="2">This is an error</error>
          </errors>
        </variant>
      </subject>
    </benchmark>
  </suite>
</phpbench>

EOT
            ),

        );
    }
}
