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

use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Console\Command\Handler\DumpHandler;
use PhpBench\Console\Command\Handler\ReportHandler;
use PhpBench\Console\Command\Handler\RunnerHandler;
use PhpBench\Console\Command\Handler\SuiteCollectionHandler;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Registry;
use PhpBench\Storage\DriverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    public const EXIT_CODE_ERROR = 1;
    public const EXIT_CODE_FAILURE = 2;

    public const OPT_ITERATIONS = 'iterations';
    public const OPT_WARMUP = 'warmup';
    public const OPT_RETRY_THRESHOLD = 'retry-threshold';
    public const OPT_SLEEP = 'sleep';
    public const OPT_TAG = 'tag';
    public const OPT_STORE = 'store';
    public const OPT_TOLERATE_FAILURE = 'tolerate-failure';

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

    /**
     * @var SuiteCollectionHandler
     */
    private $suiteCollectionHandler;

    public function __construct(
        RunnerHandler $runnerHandler,
        ReportHandler $reportHandler,
        SuiteCollectionHandler $suiteCollectionHandler,
        TimeUnitHandler $timeUnitHandler,
        DumpHandler $dumpHandler,
        Registry $storage
    ) {
        parent::__construct();
        $this->runnerHandler = $runnerHandler;
        $this->reportHandler = $reportHandler;
        $this->suiteCollectionHandler = $suiteCollectionHandler;
        $this->timeUnitHandler = $timeUnitHandler;
        $this->dumpHandler = $dumpHandler;
        $this->storage = $storage;
    }

    public function configure(): void
    {
        RunnerHandler::configure($this);
        ReportHandler::configure($this);
        SuiteCollectionHandler::configure($this);
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
        $this->addOption(self::OPT_ITERATIONS, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Override number of iteratios to run in (all) benchmarks');
        $this->addOption(self::OPT_WARMUP, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Override number of warmup revolutions on all benchmarks');
        $this->addOption(self::OPT_RETRY_THRESHOLD, 'r', InputOption::VALUE_REQUIRED, 'Set target allowable deviation', null);
        $this->addOption(self::OPT_SLEEP, null, InputOption::VALUE_REQUIRED, 'Number of microseconds to sleep between iterations');
        $this->addOption(self::OPT_TAG, null, InputOption::VALUE_REQUIRED, 'Tag to apply to stored result (useful when comparing reports)');
        $this->addOption(self::OPT_STORE, null, InputOption::VALUE_NONE, 'Persist the results');
        $this->addOption(self::OPT_TOLERATE_FAILURE, null, InputOption::VALUE_NONE, 'Return 0 exit code even when failures occur');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->timeUnitHandler->timeUnitFromInput($input);
        $this->reportHandler->validateReportsFromInput($input);

        $retryThreshold = $input->getOption(self::OPT_RETRY_THRESHOLD);
        $sleep = $input->getOption(self::OPT_SLEEP);

        $baselines = $this->resolveBaselines($input);

        $config = RunnerConfig::create()
            ->withTag((string)$input->getOption(self::OPT_TAG))
            ->withRetryThreshold($retryThreshold !== null ? (float) $retryThreshold : null)
            ->withSleep($sleep !== null ? (int) $sleep : null)
            ->withIterations($input->getOption(self::OPT_ITERATIONS))
            ->withWarmup($input->getOption(self::OPT_WARMUP))
            ->withBaselines($baselines)
            ->withAssertions($input->getOption('assert'));

        $suite = $this->runnerHandler->runFromInput($input, $output, $config);

        $collection = new SuiteCollection([$suite]);
        $this->dumpHandler->dumpFromInput($input, $output, $collection);

        if (true === $input->getOption(self::OPT_STORE) || $input->getOption(self::OPT_TAG)) {
            $output->write('Storing results ... ');

            /** @var DriverInterface $driver */
            $driver = $this->storage->getService();

            $message = $driver->store($collection);

            $output->writeln('OK');
            $output->writeln(sprintf('Run: %s', $suite->getUuid()));

            if ($message) {
                $output->writeln($message);
            }
        }


        $this->reportHandler->reportsFromInput($input, $output, $collection);

        if ($suite->getErrorStacks()) {
            return self::EXIT_CODE_ERROR;
        }

        if (false === $input->getOption(self::OPT_TOLERATE_FAILURE) && $suite->getFailures()) {
            return self::EXIT_CODE_FAILURE;
        }

        return 0;
    }

    private function resolveBaselines(InputInterface $input): SuiteCollection
    {
        if ($input->getOption('uuid') || $input->getOption('file')) {
            return $this->suiteCollectionHandler->suiteCollectionFromInput($input);
        }

        return new SuiteCollection();
    }
}
