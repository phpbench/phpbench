<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Console\Command;

use PhpBench\BenchFinder;
use PhpBench\BenchRunner;
use PhpBench\BenchSubjectBuilder;
use PhpBench\ProgressLogger\PhpUnitProgressLogger;
use PhpBench\ReportGenerator\ConsoleTableReportGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class BenchRunCommand extends Command
{
    public function configure()
    {
        $this->setName('phpbench:run');
        $this->addArgument('path', InputArgument::REQUIRED, 'Path to benchmark(s)');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Running benchmarking suite</info>');
        $output->writeln('');

        $path = $input->getArgument('path');
        $finder = new Finder();

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'File or directory "%s" does not exist',
                $path
            ));
        }

        if (is_dir($path)) {
            $finder->in($path);
            $finder->name('*Case.php');
        } else {
            $finder->in(dirname($path));
            $finder->name(basename($path));
        }

        $benchFinder = new BenchFinder($finder);
        $subjectBuilder = new BenchSubjectBuilder();
        $progressLogger = new PhpUnitProgressLogger($output);
        $generators = array(new ConsoleTableReportGenerator($output));

        $benchRunner = new BenchRunner(
            $benchFinder,
            $subjectBuilder,
            $progressLogger,
            $generators
        );

        $benchRunner->runAll();
    }
}
