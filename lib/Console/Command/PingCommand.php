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
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\BenchmarkMetadataCollection;
use PhpBench\Benchmark\RunnerContext;
use PhpBench\Benchmark\Runner;
use PhpBench\Progress\LoggerRegistry;

class PingCommand extends Command
{
    /**
     * @var LoggerRegistry
     */
    private $loggerRegistry;

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
     * @var string
     */
    private $defaultProgress;

    public function __construct(
        Runner $runner,
        LoggerRegistry $loggerRegistry,
        ReportHandler $reportHandler,
        TimeUnitHandler $timeUnitHandler,
        DumpHandler $dumpHandler,
        Registry $storage,
        $defaultProgress = null
    ) {
        parent::__construct();
        $this->runner = $runner;
        $this->loggerRegistry = $loggerRegistry;
        $this->reportHandler = $reportHandler;
        $this->timeUnitHandler = $timeUnitHandler;
        $this->dumpHandler = $dumpHandler;
        $this->storage = $storage;
        $this->defaultProgress = $defaultProgress;
    }

    public function configure()
    {
        RunnerHandler::configure($this);
        ReportHandler::configure($this);
        TimeUnitHandler::configure($this);
        DumpHandler::configure($this);

        $this->setName('ping');
        $this->setDescription('Benchmark a URL or set of URLs');
        $this->setHelp(<<<'EOT'
EOT
        );
        $this->addOption('iterations', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Override number of iteratios to run in (all) benchmarks');
        $this->addOption('warmup', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Override number of warmup revolutions on all benchmarks');
        $this->addOption('retry-threshold', 'r', InputOption::VALUE_REQUIRED, 'Set target allowable deviation', null);
        $this->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Number of microseconds to sleep between iterations');
        $this->addOption('context', null, InputOption::VALUE_REQUIRED, 'Context label to apply to the suite result (useful when comparing reports)');
        $this->addOption('store', null, InputOption::VALUE_NONE, 'Persist the results.');
        $this->addOption('url', null, InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED, 'Url(s) to ping');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->timeUnitHandler->timeUnitFromInput($input);
        $this->reportHandler->validateReportsFromInput($input);
        $urls = $input->getOption('url');

        $context = new RunnerContext(
            __DIR__,
            [
                'executor' => 'ping',
                'revolutions' => $input->getOption('revs'),
                'filters' => $input->getOption('filter'),
                'groups' => $input->getOption('group'),
                'sleep' => $input->getOption('sleep'),
                'warmup' => $input->getOption('warmup'),
                'iterations' => $input->getOption('iterations'),
                'stop_on_error' => $input->getOption('stop-on-error'),
            ]
        );

        $benchmarkMetadata = new BenchmarkMetadata(null, 'Ping');
        foreach ($urls as $url) {
            $benchmarkMetadata->getOrCreateSubject($url);
        }
        $benchmarkMetadatas = new BenchmarkMetadataCollection([$benchmarkMetadata]);

        $progressLoggerName = $input->getOption('progress') ?: $this->defaultProgress;
        $progressLogger = $this->loggerRegistry->getProgressLogger($progressLoggerName);
        $progressLogger->setOutput($output);
        $this->runner->setProgressLogger($progressLogger);

        $suite = $this->runner->run($context, $benchmarkMetadatas);

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
            return 1;
        }

        return 0;
    }
}
