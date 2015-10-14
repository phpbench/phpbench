<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\XDebug\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\CollectionBuilder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use PhpBench\Benchmark\CartesianParameterIterator;
use PhpBench\Console\Command\BaseReportCommand;

class ProfileCommand extends BaseReportCommand
{
    private $launcher;
    private $builder;

    public function __construct(
        Launcher $launcher,
        CollectionBuilder $builder
    ) {
        parent::__construct();
        $this->launcher = $launcher;
        $this->builder = $builder;
    }

    public function configure()
    {
        parent::configure();
        $this->setName('xdebug:profile');
        $this->setDescription('Generate a KCachegrind profile for the given subject');
        $this->addArgument('benchmark', InputArgument::REQUIRED, 'Path to the benchmark');
        $this->addArgument('subject', InputArgument::REQUIRED, 'The subject name');
        $this->addOption('dir', null, InputOption::VALUE_REQUIRED, 'Directory in which to dump the profile', getcwd());
        $this->addOption('kcachegrind', null, InputOption::VALUE_NONE, 'Launch KCacheGrind');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!extension_loaded('xdebug')) {
            throw new \RuntimeException(
                'You must have the XDebug extension enabled to use the profiling feature'
            );
        }

        $benchmark = $input->getArgument('benchmark');
        $subject = $input->getArgument('subject');
        $dir = $input->getOption('dir');
        $launch = $input->getOption('kcachegrind');

        $collection = $this->builder->buildCollection($benchmark, array($subject));

        if (1 !== count($collection)) {
            throw new \InvalidArgumentException(sprintf(
                'Profiler can only run one benchmark class at a time, got "%s"',
                count($collection)

            ));
        }

        $benchmark = $collection->getBenchmarks();
        $benchmark = reset($benchmark);

        if (count($benchmark->getSubjectMetadatas()) !== 1) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find subject "%s" in benchmark "%s"',
                $benchmark->getClass(),
                $subject
            ));
        }

        $subject = $benchmark->getSubjectMetadatas();
        $subject = reset($subject);
        $parameterSets = array(array());

        $parameterSets = $subject->getParameterSets() ?: array(array(array()));
        $paramsIterator = new CartesianParameterIterator($parameterSets);

        foreach ($paramsIterator as $index => $parameterSet) {
            $name = str_replace('\\', '_', $benchmark->getClass()) . '::' . $subject->getName() . '.' . $index . '.cachegrind';
            $path = $dir . '/' . $name;

            $this->launcher->launch(__DIR__ . '/../../Benchmark/Remote/template/runner.template', array(
                'class' => $benchmark->getClass(),
                'file' => $benchmark->getPath(),
                'subject' => $subject->getName(),
                'revolutions' => 1,
                'beforeMethods' => var_export($subject->getBeforeMethods(), true),
                'afterMethods' => var_export($subject->getAfterMethods(), true),
                'parameters' => var_export($parameterSet, true),
            ), array(
                'xdebug.profiler_enable' => '1',
                'xdebug.profiler_output_name' => $name,
                'xdebug.profiler_output_dir' => $dir,
                'xdebug.trace_format' => '2',
            ));

            $output->writeln($path);

            if ($launch) {
                $process = new Process('kcachegrind ' . $path);
                $process->start();
            }
        }

    }
}
