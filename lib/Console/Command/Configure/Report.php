<?php

namespace PhpBench\Console\Command\Configure;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class Report
{
    public static function configure(Command $command)
    {
        $command->addOption('report', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Report name or configuration in JSON format');
        $command->addOption('output', 'o', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Specify output', array('console'));
    }
}
