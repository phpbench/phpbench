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
use PhpBench\Console\Command\RunCommand;
use PhpBench\PhpBench;
use PhpBench\Console\Command\ReportCommand;
use PhpBench\Configuration;
use Symfony\Component\Console\Input\InputOption;
use PhpBench\Report\Generator\ConsoleTableReportGenerator;
use PhpBench\ProgressLogger\DotsProgressLogger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;
use PhpBench\Console\Output\OutputIndentDecorator;
use PhpBench\Report\Generator\ConsoleTableGenerator;

/**
 * PhpBench application.
 *
 * @author Daniel Leech <daniel@dantleech.com>
 */
class Application extends BaseApplication
{
    private $configuration;

    public function __construct(Configuration $configuration = null)
    {
        parent::__construct(
            'phpbench',
            PhpBench::VERSION
        );

        $this->add(new RunCommand());
        $this->add(new ReportCommand());

        $this->configuration = $configuration ?: new Configuration();
        $this->configuration->addReportGenerator('console_table', new ConsoleTableGenerator());
        $this->configuration->addProgressLogger('dots', new DotsProgressLogger());
        $this->configuration->addProgressLogger('benchdots', new DotsProgressLogger(true));
    }

    /**
     * Return the configuration.
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
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
