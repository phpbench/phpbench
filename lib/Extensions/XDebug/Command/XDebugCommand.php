<?php

namespace PhpBench\Extensions\XDebug\Command;

use PhpBench\Console\Command\Configure\Executor;

class XDebugCommand extends Command
{
    public function configure()
    {
        $this->setName('profile:xdebug');
        Executor::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
