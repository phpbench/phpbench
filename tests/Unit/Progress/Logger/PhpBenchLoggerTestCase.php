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

namespace PhpBench\Tests\Unit\Progress\Logger;

use PhpBench\Assertion\AssertionResult;
use PhpBench\Assertion\VariantAssertionResults;
use PhpBench\Math\Distribution;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Error;
use PhpBench\Model\ErrorStack;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\Summary;
use PhpBench\Model\Variant;
use Prophecy\Prophecy\ObjectProphecy;

abstract class PhpBenchLoggerTestCase extends LoggerTestCase
{
    protected $logger;
    protected $output;
    protected $document;
    protected $benchmark;
    protected $variant;
    protected $subject;

    /**
     * @var ParameterSet
     */
    protected $parameterSet;
    protected $stats;

    /**
     * @var ObjectProphecy<Suite>
     */
    private $suite;

    /**
     * @var ObjectProphecy<Summary>
     */
    private $summary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->suite = $this->prophesize(Suite::class);
        $this->summary = $this->prophesize(Summary::class);
        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->variant = $this->prophesize(Variant::class);
        $this->subject = $this->prophesize(Subject::class);
        $this->parameterSet = ParameterSet::fromSerializedParameters('foo', []);
        $this->stats = $this->prophesize(Distribution::class);

        $this->logger = $this->getLogger();

        $this->suite->getSummary()->willReturn($this->summary->reveal());
        $this->variant->getBaseline()->willReturn($this->variant->reveal());

        $this->stats->getMin()->willReturn(1.0);
        $this->stats->getMax()->willReturn(1.0);
        $this->stats->getVariance()->willReturn(1.0);
        $this->stats->getMean()->willReturn(1.0);
        $this->stats->getMode()->willReturn(1.0);
        $this->stats->getStdev()->willReturn(2.0);
        $this->stats->getRstdev()->willReturn(20);
    }

    abstract public function getLogger();

    /**
     * It should show the PHPBench version.
     */
    public function testStart(): void
    {
        $this->addToAssertionCount(1);
    }

    /**
     * It should show a summary at the end of the suite.
     */
    public function testEndSuite(): void
    {
        $this->setUpSummary();
        $this->suite->getFailures()->willReturn([]);
        $this->suite->getErrorStacks()->willReturn([]);
        $this->logger->endSuite($this->suite->reveal());
        self::assertNotEmpty($this->output->fetch());
    }

    public function testEndSuiteErrors(): void
    {
        $error1 = $this->prophesize(Error::class);
        $error1->getMessage()->willReturn('MessageOne');
        $error1->getTrace()->willReturn('-- trace --');

        $error2 = $this->prophesize(Error::class);
        $error2->getMessage()->willReturn('MessageTwo');
        $error2->getTrace()->willReturn('-- trace --');
        $errorStack = $this->prophesize(ErrorStack::class);
        $errorStack->getVariant()->willReturn($this->variant->reveal());
        $errorStack->getIterator()->willReturn(new \ArrayIterator([$error1->reveal(), $error2->reveal()]));

        $this->setUpSummary();
        $this->suite->getFailures()->willReturn([]);
        $this->suite->getErrorStacks()->willReturn([$errorStack]);
        $errorStack->getVariant()->willReturn($this->variant->reveal());
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->subject->getBenchmark()->willReturn($this->benchmark->reveal());
        $this->subject->getName()->willReturn('bar');
        $this->benchmark->getClass()->willReturn('Namespace\Foo');
        $this->logger->endSuite($this->suite->reveal());

        $buffer = $this->output->fetch();

        self::assertStringContainsString('1 subjects encountered errors', $buffer);
        self::assertStringContainsString('Namespace\Foo::bar', $buffer);
        self::assertStringContainsString('MessageOne', $buffer);
        self::assertStringContainsString('Two', $buffer);
    }

    public function testEndSuiteFailures(): void
    {
        $failure1 = AssertionResult::fail('Failed!');
        $failure2 = AssertionResult::fail('Failed!');
        $failures = new VariantAssertionResults($this->variant->reveal(), [$failure1, $failure2]);

        $this->setUpSummary();
        $this->suite->getFailures()->willReturn([$failures]);
        $this->suite->getErrorStacks()->willReturn([]);
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->variant->getParameterSet()->willReturn(ParameterSet::fromUnserializedValues('one', []));
        $this->subject->getBenchmark()->willReturn($this->benchmark->reveal());
        $this->subject->getName()->willReturn('bar');
        $this->benchmark->getClass()->willReturn('Namespace\Foo');



        $this->logger->endSuite($this->suite->reveal());
        $buffer = $this->output->fetch();
        self::assertStringContainsString('1 variants failed', $buffer);
    }

    private function setUpSummary(): void
    {
        $nbSubjects = 4;
        $nbIterations = 1;
        $nbRevolutions = 2;
        $nbRejects = 3;
        $min = 10;
        $max = 12;
        $mean = 11;
        $mode = 10;
        $totalTime = 123;
        $meanStDev = 321;
        $meanRelStDev = 231;
        $nbFailures = 0;
        $nbAssertions = 0;

        $this->summary->getNbSubjects()->willReturn($nbSubjects);
        $this->summary->getNbAssertions()->willReturn(0);
        $this->summary->getNbErrors()->willReturn(0);

        $this->summary->getNbIterations()->willReturn($nbIterations);
        $this->summary->getNbRevolutions()->willReturn($nbRevolutions);
        $this->summary->getNbRejects()->willReturn($nbRejects);
        $this->summary->getNbFailures()->willReturn($nbFailures);
        $this->summary->getMinTime()->willReturn($min);
        $this->summary->getMeanTime()->willReturn($mean);
        $this->summary->getModeTime()->willReturn($mode);
        $this->summary->getMaxTime()->willReturn($max);
        $this->summary->getTotalTime()->willReturn($totalTime);
        $this->summary->getMeanStDev()->willReturn($meanStDev);
        $this->summary->getMeanRelStDev()->willReturn($meanRelStDev);
    }
}
