<?php
// section: command

namespace PhpBench\Examples\Extension\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CatsCommand extends Command
{
    /**
     * @var int
     */
    private $numberOfCats;

    public function __construct(int $numberOfCats)
    {
        $this->numberOfCats = $numberOfCats;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('cats');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(str_repeat('🐈', $this->numberOfCats));

        return 0;
    }
}

// endsection: command
