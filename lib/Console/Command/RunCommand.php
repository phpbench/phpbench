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
    final public const EXIT_CODE_ERROR = 1;
    final public const EXIT_CODE_FAILURE = 2;

    final public const OPT_ITERATIONS = 'iterations';
    final public const OPT_WARMUP = 'warmup';
    final public const OPT_RETRY_THRESHOLD = 'retry-threshold';
    final public const OPT_SLEEP = 'sleep';
    final public const OPT_TAG = 'tag';
    final public const OPT_STORE = 'store';
    final public const OPT_TOLERATE_FAILURE = 'tolerate-failure';

    /**
     * @param Registry<DriverInterface> $storage
     */
    public function __construct(
        private readonly RunnerHandler $runnerHandler,
        private readonly ReportHandler $reportHandler,
        private readonly SuiteCollectionHandler $suiteCollectionHandler,
        private readonly TimeUnitHandler $timeUnitHandler,
        private readonly DumpHandler $dumpHandler,
        private readonly Registry $storage
    ) {
        parent::__construct();
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
        $this->setHelp(
            <<<'EOT'
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

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->timeUnitHandler->timeUnitFromInput($input);
        $this->reportHandler->validateReportsFromInput($input);

        /** @var string|null $retryThreshold */
        $retryThreshold = $input->getOption(self::OPT_RETRY_THRESHOLD);

        /** @var string|null $sleep */
        $sleep = $input->getOption(self::OPT_SLEEP);

        /** @var list<string> $iterations */
        $iterations = $input->getOption(self::OPT_ITERATIONS);
        $iterations = array_map('intval', $iterations);

        /** @var list<string> $warmup */
        $warmup = $input->getOption(self::OPT_WARMUP);
        $warmup = array_map('intval', $warmup);

        /** @var string|null $tag */
        $tag = $input->getOption(self::OPT_TAG);

        /** @var bool $store */
        $store = $input->getOption(self::OPT_STORE);

        /** @var bool $tolerateFailure */
        $tolerateFailure = $input->getOption(self::OPT_TOLERATE_FAILURE);

        $baselines = $this->resolveBaselines($input);

        $config = RunnerConfig::create()
            ->withTag((string)$tag)
            ->withRetryThreshold($retryThreshold !== null ? (float) $retryThreshold : null)
            ->withSleep($sleep !== null ? (int) $sleep : null)
            ->withIterations($iterations)
            ->withWarmup($warmup)
            ->withBaselines($baselines);

        $suite = $this->runnerHandler->runFromInput($input, $output, $config);

        $collection = new SuiteCollection([$suite]);
        $this->dumpHandler->dumpFromInput($input, $output, $collection);

        if (true === $store || $tag) {
            $output->write('Storing results ... ');

            $driver = $this->storage->getService();

            $message = $driver->store($collection);

            $output->writeln('OK');
            $output->writeln(sprintf('Run: %s', $suite->getUuid()));

            if ($message) {
                $output->writeln($message);
            }
        }

        if ($suite->getErrorStacks()) {
            return self::EXIT_CODE_ERROR;
        }

        $this->reportHandler->reportsFromInput($input, $collection->mergeCollection($this->resolveBaselines($input)));

        if (false === $tolerateFailure && $suite->getFailures()) {
            return self::EXIT_CODE_FAILURE;
        }

        return 0;
    }

    private function resolveBaselines(InputInterface $input): SuiteCollection
    {
        if ($input->getOption('ref') || $input->getOption('file')) {
            return $this->suiteCollectionHandler->suiteCollectionFromInput($input);
        }

        return new SuiteCollection();
    }
}
