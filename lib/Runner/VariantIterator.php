<?php

namespace PhpBench\Runner;

use IteratorAggregate;
use PhpBench\Benchmark\Metadata\SubjectMetadata;

class VariantIterator implements IteratorAggregate
{
    /**
     * @var SubjectIterator<SubjectMetadata>
     */
    private $subjectIterator;

    public function __construct(SubjectIterator $subjectIterator)
    {
        $this->subjectIterator = $subjectIterator;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        foreach ($this->subjectIterator as $subjectMetadata) {
            $parameterSets = new CartesianParameterIterator($subjectMetadata->getParameterSets());

            foreach ($parameterSets as $parameterSet) {
                foreach ($subjectMetadata->getIterations() as $nbIterations) {
                    foreach ($subjectMetadata->getRevs() as $revolutions) {
                        foreach ($subjectMetadata->getWarmup() as $warmup) {
                            $variant = new Variant();
                            $variant->subjectName = $subjectMetadata->getName();

                            $variant->parameters = $parameterSet;
                            $variant->iterations = $nbIterations;
                            $variant->revolutions = $revolutions;

                            $variant->executor = $subjectMetadata->getExecutor()->getName();
                            $variant->executorConfig = $subjectMetadata->getExecutor()->getConfig();

                            $variant->class = $subjectMetadata->getBenchmark()->getClass();
                            $variant->sleep = $subjectMetadata->getSleep();
                            $variant->afterMethods = $subjectMetadata->getAfterMethods();
                            $variant->beforeMethods = $subjectMetadata->getBeforeMethods();

                            yield $variant;
                        }
                    }
                }
            }
        }
    }
}
