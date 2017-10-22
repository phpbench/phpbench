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
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
            PhpBench::VERSION
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        $default = parent::getDefaultInputDefinition();
        $default->addOptions([
            new InputOption('--config', null, InputOption::VALUE_REQUIRED, 'Configuration file'),
            new InputOption('--extension', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Enable an extension'),
        ]);

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        parent::configureIO($input, $output);
        $output->getFormatter()->setStyle('greenbg', new OutputFormatterStyle('black', 'green', []));
        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle('black', 'yellow', []));
    }
}
