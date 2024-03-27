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
use PhpBench\Storage\DriverInterface;
use PhpBench\Storage\UuidResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to show/report on a specific run.
 */
class ShowCommand extends Command
{
    private const ARG_RUN_ID = 'run_id';

    /**
     * @param Registry<DriverInterface> $storage
     */
    public function __construct(
        private readonly Registry $storage,
        private readonly ReportHandler $reportHandler,
        private readonly TimeUnitHandler $timeUnitHandler,
        private readonly DumpHandler $dumpHandler,
        private readonly UuidResolver $refResolver
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this->setName('show');
        $this->setDescription('Show the details of a specific run.');
        $this->addArgument(self::ARG_RUN_ID, InputArgument::REQUIRED, 'Run ID');
        $this->setHelp(
            <<<'EOT'
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
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->getOption('report')) {
            $input->setOption('report', ['aggregate']);
        }

        /** @var string $runId */
        $runId = $input->getArgument(self::ARG_RUN_ID);

        $storage = $this->storage->getService();
        $collection = $storage->fetch($this->refResolver->resolve($runId));
        $this->timeUnitHandler->timeUnitFromInput($input);
        $this->dumpHandler->dumpFromInput($input, $output, $collection);
        $this->reportHandler->reportsFromInput($input, $collection);

        return 0;
    }
}
