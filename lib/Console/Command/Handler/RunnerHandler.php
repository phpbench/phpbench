<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Console\Command\Handler;

use PhpBench\Benchmark\Runner;
use PhpBench\Benchmark\RunnerContext;
use PhpBench\Progress\LoggerRegistry;
use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunnerHandler
{
    private $loggerRegistry;
    private $defaultProgress;
    private $benchPath;
    private $runner;
    private $timeUnit;

    public function __construct(
        Runner $runner,
        LoggerRegistry $loggerRegistry,
        TimeUnit $timeUnit,
        $defaultProgress = null,
        $benchPath = null
    ) {
        $this->runner = $runner;
        $this->loggerRegistry = $loggerRegistry;
        $this->timeUnit = $timeUnit;
        $this->defaultProgress = $defaultProgress;
        $this->benchPath = $benchPath;
    }

    public static function configure(Command $command)
    {
        $command->addArgument('path', InputArgument::OPTIONAL, 'Path to benchmark(s)');
        $command->addOption('filter', array(), InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Ignore all benchmarks not matching command filter (can be a regex)');
        $command->addOption('group', array(), InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Group to run (can be specified multiple times)');
        $command->addOption('parameters', null, InputOption::VALUE_REQUIRED, 'Override parameters to use in (all) benchmarks');
        $command->addOption('revs', null, InputOption::VALUE_REQUIRED, 'Override number of revs (revolutions) on (all) benchmarks');
        $command->addOption('time-unit', null, InputOption::VALUE_REQUIRED, 'Override the time unit');
        $command->addOption('mode', null, InputOption::VALUE_REQUIRED, 'Override the unit display mode ("throughput", "time")');
        $command->addOption('progress', 'l', InputOption::VALUE_REQUIRED, 'Progress logger to use, one of <comment>dots</comment>, <comment>classdots</comment>');

        // command option is parsed before the container is compiled.
        $command->addOption('bootstrap', 'b', InputOption::VALUE_REQUIRED, 'Set or override the bootstrap file.');
        $command->addOption('group', array(), InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Group to run (can be specified multiple times)');
    }

    public function runFromInput(InputInterface $input, OutputInterface $output, array $options = array())
    {
        $context = new RunnerContext(
            $input->getArgument('path') ?: $this->benchPath,
            array_merge(
                array(
                    'parameters' => $this->getParameters($input->getOption('parameters')),
                    'revolutions' => $input->getOption('revs'),
                    'filters' => $input->getOption('filter'),
                    'groups' => $input->getOption('group'),
                ),
                $options
            )
        );

        $progressLoggerName = $input->getOption('progress') ?: $this->defaultProgress;
        $timeUnit = $input->getOption('time-unit');
        $mode = $input->getOption('mode');

        if ($timeUnit) {
            $this->timeUnit->overrideDestUnit($timeUnit);
        }

        if ($mode) {
            $this->timeUnit->overrideMode($mode);
        }

        $progressLogger = $this->loggerRegistry->getProgressLogger($progressLoggerName);
        $progressLogger->setOutput($output);
        $this->runner->setProgressLogger($progressLogger);

        return $this->runner->run($context);
    }

    private function getParameters($parametersJson)
    {
        if (null === $parametersJson) {
            return;
        }

        $parameters = array();
        if ($parametersJson) {
            $parameters = json_decode($parametersJson, true);
            if (null === $parameters) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not decode parameters JSON string: "%s"', $parametersJson
                ));
            }
        }

        return $parameters;
    }
}
