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
use PhpBench\Console\Command\Handler\RunnerHandler;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Model\SuiteCollection;
use PhpBench\PhpBench;
use PhpBench\Registry\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    const EXIT_CODE_ERROR = 1;
    const EXIT_CODE_FAILURE = 2;

    /**
     * @var RunnerHandler
     */
    private $runnerHandler;

    /**
     * @var ReportHandler
     */
    private $reportHandler;

    /**
     * @var TimeUnitHandler
     */
    private $timeUnitHandler;

    /**
     * @var DumpHandler
     */
    private $dumpHandler;

    /**
     * @var Registry
     */
    private $storage;

    public function __construct(
        RunnerHandler $runnerHandler,
        ReportHandler $reportHandler,
        TimeUnitHandler $timeUnitHandler,
        DumpHandler $dumpHandler,
        Registry $storage
    ) {
        parent::__construct();
        $this->runnerHandler = $runnerHandler;
        $this->reportHandler = $reportHandler;
        $this->timeUnitHandler = $timeUnitHandler;
        $this->dumpHandler = $dumpHandler;
        $this->storage = $storage;
    }

    public function configure()
    {
        RunnerHandler::configure($this);
        ReportHandler::configure($this);
        TimeUnitHandler::configure($this);
        DumpHandler::configure($this);

        $this->setName('run');
        $this->setDescription('Run benchmarks');
        $this->setHelp(<<<'EOT'
Run benchmark files at given <comment>path</comment>

    $ %command.full_name% /path/to/bench

All bench marks under the given path will be executed recursively.
EOT
        );
        $this->addOption('iterations', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Override number of iteratios to run in (all) benchmarks');
        $this->addOption('warmup', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Override number of warmup revolutions on all benchmarks');
        $this->addOption('retry-threshold', 'r', InputOption::VALUE_REQUIRED, 'Set target allowable deviation', null);
        $this->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Number of microseconds to sleep between iterations');
        $this->addOption('context', null, InputOption::VALUE_REQUIRED, 'Context label to apply to the suite result (useful when comparing reports)');
        $this->addOption('store', null, InputOption::VALUE_NONE, 'Persist the results.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->timeUnitHandler->timeUnitFromInput($input);
        $this->reportHandler->validateReportsFromInput($input);
        $suite = $this->runnerHandler->runFromInput($input, $output, [
            'context_name' => $input->getOption('context'),
            'retry_threshold' => $input->getOption('retry-threshold'),
            'sleep' => $input->getOption('sleep'),
            'iterations' => $input->getOption('iterations'),
            'warmup' => $input->getOption('warmup'),
            'assertions' => $input->getOption('assert'),
        ]);

        $collection = new SuiteCollection([$suite]);

        $this->dumpHandler->dumpFromInput($input, $output, $collection);

        if (true === $input->getOption('store')) {
            $output->write('Storing results ... ');
            $this->storage->getService()->store($collection);
            $output->writeln('OK');
            $output->writeln(sprintf('Run: %s', $suite->getUuid()));
        }

        $this->reportHandler->reportsFromInput($input, $output, $collection);

        if ($suite->getErrorStacks()) {
            return self::EXIT_CODE_ERROR;
        }

        if ($suite->getFailures()) {
            return self::EXIT_CODE_FAILURE;
        }

        return 0;
    }
}
