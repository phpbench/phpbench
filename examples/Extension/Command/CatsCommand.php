<?php
// section: command

namespace PhpBench\Examples\Extension\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CatsCommand extends Command
{
    public function __construct(private readonly int $numberOfCats)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('cats');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(str_repeat('🐈', $this->numberOfCats));

        return 0;
    }
}

// endsection: command
