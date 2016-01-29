<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Console\Command;

use PhpBench\Console\Command\Handler\ReportHandler;
use PhpBench\Console\Command\Handler\RunnerHandler;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Model\SuiteCollection;
use PhpBench\PhpBench;
use PhpBench\Serializer\XmlEncoder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunCommand extends Command
{
    private $runnerHandler;
    private $reportHandler;
    private $timeUnitHandler;
    private $xmlEncoder;

    public function __construct(
        RunnerHandler $runnerHandler,
        ReportHandler $reportHandler,
        TimeUnitHandler $timeUnitHandler,
        XmlEncoder $xmlEncoder
    ) {
        parent::__construct();
        $this->runnerHandler = $runnerHandler;
        $this->reportHandler = $reportHandler;
        $this->timeUnitHandler = $timeUnitHandler;
        $this->xmlEncoder = $xmlEncoder;
    }

    public function configure()
    {
        RunnerHandler::configure($this);
        ReportHandler::configure($this);
        TimeUnitHandler::configure($this);

        $this->setName('run');
        $this->setDescription('Run benchmarks');
        $this->setHelp(<<<'EOT'
Run benchmark files at given <comment>path</comment>

    $ %command.full_name% /path/to/bench

All bench marks under the given path will be executed recursively.
EOT
        );
        $this->addOption('dump-file', 'd', InputOption::VALUE_OPTIONAL, 'Dump XML result to named file');
        $this->addOption('dump', null, InputOption::VALUE_NONE, 'Dump XML result to stdout and suppress all other output');
        $this->addOption('iterations', null, InputOption::VALUE_REQUIRED, 'Override number of iteratios to run in (all) benchmarks');
        $this->addOption('warmup', null, InputOption::VALUE_REQUIRED, 'Override number of warmup revolutions on all benchmarks');
        $this->addOption('retry-threshold', 'r', InputOption::VALUE_REQUIRED, 'Set target allowable deviation', null);
        $this->addOption('sleep', null, InputOption::VALUE_REQUIRED, 'Number of microseconds to sleep between iterations');
        $this->addOption('context', null, InputOption::VALUE_REQUIRED, 'Context label to apply to the suite result (useful when comparing reports)');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->timeUnitHandler->timeUnitFromInput($input);
        $suite = $this->runnerHandler->runFromInput($input, $output, array(
            'context_name' => $input->getOption('context'),
            'retry_threshold' => $input->getOption('retry-threshold'),
            'sleep' => $input->getOption('sleep'),
            'iterations' => $input->getOption('iterations'),
            'warmup' => $input->getOption('warmup'),
        ));

        $collection = new SuiteCollection(array($suite));

        if ($input->getOption('dump-file') || $input->getOption('dump')) {
            $dom = $this->xmlEncoder->encode($collection);
        }

        if ($dumpFile = $input->getOption('dump-file')) {
            $xml = $dom->dump();
            file_put_contents($dumpFile, $xml);
            $output->writeln('Dumped result to ' . $dumpFile);
        }

        $this->reportHandler->reportsFromInput($input, $output, $collection);

        if ($input->getOption('dump')) {
            $xml = $dom->dump();
            $output->write($xml);
        }

        if ($suite->getErrorStacks()) {
            return 1;
        }

        return 0;
    }
}
