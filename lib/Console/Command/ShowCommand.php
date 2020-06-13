<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Console\Command;

use PhpBench\Console\Command\Handler\DumpHandler;
use PhpBench\Console\Command\Handler\ReportHandler;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Registry\Registry;
use PhpBench\Storage\UuidResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to show/report on a specific run.
 */
class ShowCommand extends Command
{
    private $storage;
    private $reportHandler;
    private $timeUnitHandler;
    private $dumpHandler;
    private $uuidResolver;

    public function __construct(
        Registry $storage,
        ReportHandler $reportHandler,
        TimeUnitHandler $timeUnitHandler,
        DumpHandler $dumpHandler,
        UuidResolverInterface $uuidResolver
    ) {
        parent::__construct();
        $this->storage = $storage;
        $this->reportHandler = $reportHandler;
        $this->timeUnitHandler = $timeUnitHandler;
        $this->dumpHandler = $dumpHandler;
        $this->uuidResolver = $uuidResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('show');
        $this->setDescription('Show the details of a specific run.');
        $this->addArgument('run_id', InputArgument::REQUIRED, 'Run ID');
        $this->setHelp(<<<'EOT'
Show the results of a specific run.

    $ %command.full_name% <run id>

Any report can be used to display the results:

    $ %command.full_name% <run id> --report=env

EOT
        );
        ReportHandler::configure($this);
        TimeUnitHandler::configure($this);
        DumpHandler::configure($this);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('report')) {
            $input->setOption('report', ['aggregate']);
        }

        $storage = $this->storage->getService();
        $collection = $storage->fetch($this->uuidResolver->resolve($input->getArgument('run_id')));
        $this->timeUnitHandler->timeUnitFromInput($input);
        $this->dumpHandler->dumpFromInput($input, $output, $collection);
        $this->reportHandler->reportsFromInput($input, $output, $collection);

        return 0;
    }
}
