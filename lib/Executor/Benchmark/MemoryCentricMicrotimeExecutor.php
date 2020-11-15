<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Remote\Launcher;

class MemoryCentricMicrotimeExecutor extends TemplateExecutor
{
    public function __construct(Launcher $launcher)
    {
        parent::__construct($launcher, __DIR__ . '/template/memory.template');
    }
}
