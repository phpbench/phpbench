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
use PhpBench\ReportGenerator\ConsoleTableReportGenerator;
use PhpBench\ProgressLogger\DotsProgressLogger;
use PhpBench\ProgressLogger\DetailedProgressLogger;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

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
        $this->configuration->addReportGenerator('console_table', new ConsoleTableReportGenerator());
        $this->configuration->addProgressLogger('dots', new DotsProgressLogger());
        $this->configuration->addProgressLogger('casedots', new DotsProgressLogger(true));
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    protected function getDefaultInputDefinition()
    {
        $default = parent::getDefaultInputDefinition();
        $default->addOptions(array(
            new InputOption('--config', null, InputOption::VALUE_REQUIRED),
        ));

        return $default;
    }

    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        $output->getFormatter()->setStyle('greenbg', new OutputFormatterStyle('black', 'green', array()));
    }
}
