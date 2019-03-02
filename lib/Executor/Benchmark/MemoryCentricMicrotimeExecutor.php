<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Model\Iteration;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemoryCentricMicrotimeExecutor extends TemplateExecutor
{
    public function __construct(Launcher $launcher)
    {
        parent::__construct($launcher, __DIR__ . '/template/memory.template');
    }
}
