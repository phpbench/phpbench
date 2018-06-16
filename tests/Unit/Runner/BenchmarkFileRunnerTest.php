<?php

namespace PhpBench\Tests\Unit\Runner;

use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use PhpBench\Benchmark\Metadata\ExecutorMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Registry\ConfigResolverInterface;
use PhpBench\Registry\RegistryInterface;
use PhpBench\Runner\BenchmarkFileRunner;
use PhpBench\Runner\Sampler;
use PhpBench\Runner\Sampler\DebugSampler;
use PhpBench\Runner\Scheduler\LinearScheduler;
use PhpBench\Runner\Stage\AggregateStage;
use PhpBench\Runner\Stage\IterationLimiter;
use PhpBench\Runner\Stage\SamplerStage;
use PhpBench\Runner\SubjectIterator;
use PhpBench\Runner\SubjectIteratorFactory;
use PhpBench\Tests\Util\TestUtil;

class BenchmarkFileRunnerTest extends TestCase
{
    const EXAMPLE_PATH = 'path/to';
    const FOOBAR_SAMPLER = 'foobar_sampler';


    /**
     * @var ObjectProphecy|SubjectIteratorFactory
     */
    private $metadataIteratorFactory;

    /**
     * @var ObjectProphecy|RegistryInterface
     */
    private $samplerRegistry;

    /**
     * @var ObjectProphecy|ConfigResolverInterface
     */
    private $samplerConfigResolver;

    /**
     * @var BenchmarkFileRunner
     */
    private $runner;

    public function setUp()
    {
        $this->metadataIteratorFactory = $this->prophesize(SubjectIteratorFactory::class);
        $this->samplerRegistry = $this->prophesize(RegistryInterface::class);
        $this->samplerConfigResolver = $this->prophesize(ConfigResolverInterface::class);

        $this->runner = new BenchmarkFileRunner(
            $this->metadataIteratorFactory->reveal(),
            $this->samplerRegistry->reveal(),
            $this->samplerConfigResolver->reveal()
        );

        $this->sampler = $this->prophesize(Sampler::class);
        $this->subjectIterator = $this->prophesize(SubjectIterator::class);
    }

    /**
     * TODO: Consider making making this test use real instances.
     */
    public function testRun()
    {
        $subjectMetadata = $this->prophesize(SubjectMetadata::class);
        TestUtil::configureSubjectMetadata($subjectMetadata);
        $subjectMetadata->getExecutor()->willReturn(new ExecutorMetadata(self::FOOBAR_SAMPLER, []));

        $this->subjectIterator->getIterator()->will(function () use ($subjectMetadata) {
            yield $subjectMetadata->reveal();
        });

        $this->metadataIteratorFactory->subjectIterator(self::EXAMPLE_PATH)->willReturn(
            $this->subjectIterator->reveal()
        );

        $this->samplerRegistry->getService(self::FOOBAR_SAMPLER)->willReturn($this->sampler->reveal());
        $samplerConfig = ['config' => 'config'];
        $this->samplerConfigResolver->getConfig([])->willReturn($samplerConfig);

        $scheduler = $this->runner->build(self::EXAMPLE_PATH);

        $this->assertInstanceOf(LinearScheduler::class, $scheduler);
    }

}
