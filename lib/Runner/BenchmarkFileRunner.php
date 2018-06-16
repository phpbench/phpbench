<?php

namespace PhpBench\Runner;

use PhpBench\Benchmark\Metadata\MetadataFactory;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Registry\ConfigResolverInterface;
use PhpBench\Registry\RegistryInterface;
use PhpBench\Runner\Scheduler\LinearScheduler;
use PhpBench\Runner\Stage\AggregateStage;
use PhpBench\Runner\Stage\IterationLimiter;
use PhpBench\Runner\Stage\SamplerStage;

class BenchmarkFileRunner
{
    /**
     * @var SubjectIteratorFactory
     */
    private $metadataIteratorFactory;

    /**
     * @var RegistryInterface
     */
    private $samplerRegistry;

    /**
     * @var ConfigResolverInterface
     */
    private $samplerConfigResolver;

    public function __construct(
        SubjectIteratorFactory $metadataIteratorFactory,
        RegistryInterface $samplerRegistry,
        ConfigResolverInterface $samplerConfigResolver
    )
    {
        $this->metadataIteratorFactory = $metadataIteratorFactory;
        $this->samplerRegistry = $samplerRegistry;
        $this->samplerConfigResolver = $samplerConfigResolver;
    }

    public function build(string $path): Scheduler
    {
        $metadataIterator = $this->metadataIteratorFactory->subjectIterator($path);
        $stageAggregates = [];

        /** @var SubjectMetadata $subjectMetadata */
        foreach ($metadataIterator as $subjectMetadata) {
            $samplerStage = $this->resolveSamplerStage($subjectMetadata);
            $stages = [ $samplerStage ];
            $stages[] = new IterationLimiter($subjectMetadata->getIterations());

            $stageAggregates[] = new AggregateStage($stages);
        }

        return new LinearScheduler($stages);
    }

    private function resolveSamplerStage(SubjectMetadata $subjectMetadata)
    {
        $sampler = $this->samplerRegistry->getService($subjectMetadata->getExecutor()->getName());
        $samplerConfig = $this->samplerConfigResolver->getConfig($subjectMetadata->getExecutor()->getConfig());
        $samplerStage = new SamplerStage($sampler, $samplerConfig);
        return $samplerStage;
    }
}
