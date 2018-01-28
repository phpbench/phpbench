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
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Model\SuiteCollection;
use PhpBench\PhpBench;
use PhpBench\Registry\Registry;
use RuntimeException;
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

    public function __construct(RunnerHandler $runnerHandler, ReportHandler $reportHandler, TimeUnitHandler $timeUnitHandler, DumpHandler $dumpHandler, Registry $storage
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
        $this->addOption('context', null, InputOption::VALUE_REQUIRED, 'DEPRECATED! Use tag instead.');
        $this->addOption('tag', null, InputOption::VALUE_REQUIRED, 'Tag to apply to stored result (useful when comparing reports)');
        $this->addOption('store', null, InputOption::VALUE_NONE, 'Persist the results');
        $this->addOption('tolerate-failure', null, InputOption::VALUE_NONE, 'Return 0 exit code even when failures occur');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->timeUnitHandler->timeUnitFromInput($input);
        $this->reportHandler->validateReportsFromInput($input);

        $config = RunnerConfig::create()
            ->withTag($this->resolveTag($input))
            ->withRetryThreshold($input->getOption('retry-threshold'))
            ->withSleep($input->getOption('sleep'))
            ->withIterations($input->getOption('iterations'))
            ->withWarmup($input->getOption('warmup'))
            ->withAssertions($input->getOption('assert'));

        $suite = $this->runnerHandler->runFromInput($input, $output, $config);

        $collection = new SuiteCollection([$suite]);
        $this->dumpHandler->dumpFromInput($input, $output, $collection);

        if (true === $input->getOption('store')) {
            $output->write('Storing results ... ');
            $message = $this->storage->getService()->store($collection);

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

        if (false === $input->getOption('tolerate-failure') && $suite->getFailures()) {
            return self::EXIT_CODE_FAILURE;
        }

        return 0;
    }

    private function resolveTag(InputInterface $input)
    {
        $tag = $input->getOption('tag');
        $context = $input->getOption('context');

        if ($tag && $context) {
            throw new RuntimeException(
                'Options `tag` and `context` are synonyms (and context is deprecated), you cannot use them both'
            );
        }

        if ($context) {
            return $context;
        }

        return $tag;
    }
}
