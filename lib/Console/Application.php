<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Console;

use Symfony\Component\Console\Application as BaseApplication;
use PhpBench\PhpBench;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use PhpBench\Console\Output\OutputIndentDecorator;

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
     * {@inheritDoc}
     */
    protected function getDefaultInputDefinition()
    {
        $default = parent::getDefaultInputDefinition();
        $default->addOptions(array(
            new InputOption('--config', null, InputOption::VALUE_REQUIRED),
        ));

        return $default;
    }

    /**
     * {@inheritDoc}
     */
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        parent::configureIO($input, $output);
        $output->getFormatter()->setStyle('greenbg', new OutputFormatterStyle('black', 'green', array()));
    }

    /**
     * {@inheritDoc}
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        $output = new OutputIndentDecorator(new ConsoleOutput());

        return parent::run($input, $output);
    }
}
