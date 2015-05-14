<?php

namespace PhpBench\Console;

use Symfony\Component\Console\Application as BaseApplication;
use PhpBench\Console\Command\RunCommand;
use PhpBench\PhpBench;
use PhpBench\Console\Command\ReportCommand;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct(
            'phpbench',
            PhpBench::VERSION
        );

        $this->add(new RunCommand());
        $this->add(new ReportCommand());
    }
}
