<?php

namespace PhpBench\Console\Command\Configure;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Executor
{
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
    }
}
