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

namespace PhpBench\Console;

use PhpBench\PhpBench;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;

/**
 * PhpBench application.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct(
            'phpbench',
            PhpBench::version()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition(): \Symfony\Component\Console\Input\InputDefinition
    {
        $default = parent::getDefaultInputDefinition();
        $default->addOptions([
            new InputOption('--profile', null, InputOption::VALUE_REQUIRED, 'Configuration file'),
            new InputOption('--config', null, InputOption::VALUE_REQUIRED, 'Use the specified configuration profile'),
            new InputOption('--extension', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Enable an extension'),
            new InputOption('--theme', null, InputOption::VALUE_REQUIRED, 'Theme'),
            new InputOption('--working-dir', null, InputOption::VALUE_REQUIRED, 'Working directory'),
        ]);

        return $default;
    }
}
