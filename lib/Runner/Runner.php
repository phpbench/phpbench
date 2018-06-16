<?php

namespace PhpBench\Runner;

use PhpBench\Benchmark\Metadata\MetadataFactory;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Registry\ConfigResolverInterface;
use PhpBench\Registry\RegistryInterface;
use PhpBench\Runner\Stage\IterationLimiter;
use PhpBench\Runner\Stage\SamplerStage;

class Runner
{
    /**
     * @var SubjectIteratorFactory
     */
    private $subjectIteratorFactory;

    /**
     * @var RegistryInterface
     */
    private $samplerRegistry;

    /**
     * @var ConfigResolverInterface
     */
    private $samplerConfigResolver;

    public function __construct(
        SubjectIteratorFactory $subjectIteratorFactory,
        RegistryInterface $samplerRegistry,
        ConfigResolverInterface $samplerConfigResolver
    )
    {
        $this->subjectIteratorFactory = $subjectIteratorFactory;
        $this->samplerRegistry = $samplerRegistry;
        $this->samplerConfigResolver = $samplerConfigResolver;
    }

    public function run(string $path)
    {
        $subjectIterator = $this->subjectIteratorFactory->subjectIterator($path);
        $stageAggregates = [];

        /** @var SubjectMetadata $subjectMetadata */
        foreach ($subjectIterator as $subjectMetadata) {

            $samplerStage = $this->resolveSamplerStage($subjectMetadata);
            $stages = [ $samplerStage ];
            $stages[] = new IterationLimiter($subjectMetadata->getIterations());

            $stageAggregates[] = new AggregateStage($stages);
        }

        $scheduler = new LinearScheduler($stages);
        $scheduler->run();
    }

    private function resolveSamplerStage(SubjectMetadata $subjectMetadata)
    {
        $sampler = $this->samplerRegistry->get($subjectMetadata->executor()->name());
        $samplerConfig = $this->samplerConfigResolver->getConfig($subjectMetadata->getExecutor()->getConfig());
        $samplerStage = new SamplerStage($sampler, $samplerConfig);
        return $samplerStage;
    }
}
