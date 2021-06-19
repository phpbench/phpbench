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

namespace PhpBench\Executor\Benchmark;

use PhpBench\Remote\Launcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RemoteExecutor extends TemplateExecutor
{
    public function __construct(Launcher $launcher)
    {
        parent::__construct($launcher, __DIR__ . '/template/remote.template');
    }

    public function configure(OptionsResolver $options): void
    {
        parent::configure($options);
        $options->setDefaults([
            self::OPTION_SAFE_PARAMETERS => true,
        ]);
    }
}
