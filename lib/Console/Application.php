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
use Symfony\Component\Console\Input\InputInterface;
use PhpBench\Benchmark\Configuration;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\ReportGenerator\XmlTableReportGenerator;
use Symfony\Component\Console\Input\InputOption;
use PhpBench\ReportGenerator\ConsoleTableReportGenerator;

class Application extends BaseApplication
{
    private $configuration;

    public function __construct()
    {
        parent::__construct(
            'phpbench',
            PhpBench::VERSION
        );

        $this->add(new RunCommand());
        $this->add(new ReportCommand());
    }

    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->initConfiguration();
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function initConfiguration()
    {
        $configuration = $this->getConfiguration();
        $configuration->addReportGenerator('console_table', new ConsoleTableReportGenerator());
        $configuration->addReportGenerator('xml_table', new XmlTableReportGenerator());
    }

    protected function getDefaultInputDefinition()
    {
        $default = parent::getDefaultInputDefinition();
        $default->addOptions(array(
            new InputOption('--config', null, InputOption::VALUE_REQUIRED)
        ));

        return $default;
    }
}
