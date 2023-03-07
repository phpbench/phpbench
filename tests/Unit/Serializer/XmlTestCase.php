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

use DateTime;
use PhpBench\Assertion\AssertionResult;
use PhpBench\Assertion\VariantAssertionResults;
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
use PhpBench\Model\Tag;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PhpBench\Tests\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

abstract class XmlTestCase extends TestCase
{
    /**
     * @var ObjectProphecy<SuiteCollection>
     */
    private $suiteCollection;
    /**
     * @var ObjectProphecy<Suite>
     */
    private $suite;
    /**
     * @var ObjectProphecy<Information>
     */
    private $env1;
    /**
     * @var ObjectProphecy<Benchmark>
     */
    private $bench1;
    /**
     * @var ObjectProphecy<Subject>
     */
    private $subject1;
    /**
     * @var ObjectProphecy<Variant>
     */
    private $variant1;
    /**
     * @var ObjectProphecy<Iteration>
     */
    private $iteration1;

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
            'groups' => [],
            'params' => [],
        ], $params);

        $this->suiteCollection->getSuites()->willReturn([$this->suite->reveal()]);
        $this->suite->getUuid()->willReturn(1234);
        $this->suite->getDate()->willReturn(new \DateTime('2015-01-01T00:00:00+00:00'));
        $this->suite->getTag()->willReturn(new Tag('test'));
        $this->suite->getConfigPath()->willReturn('/path/to/config.json');
        $this->suite->getEnvInformations()->willReturn([
            $this->env1,
        ]);
        $this->env1->getName()->willReturn('info1');
        $this->env1->getIterator()->willReturn(new \ArrayIterator([
            'foo' => 'fooo & bar',
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
        $this->variant1->getParameterSet()->willReturn(ParameterSet::fromUnserializedValues('some params', $params['params']));
        $this->variant1->hasErrorStack()->willReturn($params['error']);

        if ($params['failure']) {
            $this->variant1->getAssertionResults()->willReturn(new VariantAssertionResults($this->variant1->reveal(), [AssertionResult::fail()]));
        } else {
            $this->variant1->getAssertionResults()->willReturn(new VariantAssertionResults($this->variant1->reveal(), []));
        }

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
                            0,
                            1,
                            2,
                            '-- trace --'
                        ),
                    ]
                )
            );
        }

        $results = [];

        if ($params['failure']) {
            $results[] = AssertionResult::fail('Fail!');
        }

        $this->variant1->getAssertionResults()->willReturn(new VariantAssertionResults($this->variant1->reveal(), $results));

        $this->variant1->getStats()->willReturn(new Distribution([0.1]));
        $this->variant1->getIterator()->willReturn(new \ArrayIterator([
            $this->iteration1->reveal(),
        ]));
        $this->iteration1->getResults()->willReturn([
            new TimeResult(10, 1),
            new MemoryResult(100, 110, 109),
            new ComputedResult(0, 0, 5),
        ]);

        return $this->suiteCollection->reveal();
    }

    public function provideEncode()
    {
        foreach (glob(__DIR__ . '/examples/*.test') as $path) {
            yield [
                $path
            ];
        }
    }

    public function testBinary(): void
    {
        $collection = $this->getSuiteCollection([
            'params' => [
                'foo' => "\x80",
            ]
        ]);
        $this->doTestBinary($collection);
    }

    public function testDate(): void
    {
        $collection = $this->getSuiteCollection([
            'params' => [
                'foo' => new DateTime('2021-01-01 00:00:00+00:00'),
            ]
        ]);
        $this->doTestDate($collection);
    }

    abstract public function doTestBinary(SuiteCollection $collection): void;
    abstract public function doTestDate(SuiteCollection $collection): void;
}
