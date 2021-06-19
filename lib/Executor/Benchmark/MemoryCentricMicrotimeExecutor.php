<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Remote\Launcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemoryCentricMicrotimeExecutor extends TemplateExecutor
{
    public function __construct(Launcher $launcher)
    {
        parent::__construct($launcher, __DIR__ . '/template/memory.template');
    }

    public function configure(OptionsResolver $options): void
    {
        parent::configure($options);
        $options->setDefaults([
            self::OPTION_SAFE_PARAMETERS => true,
        ]);
    }
}
