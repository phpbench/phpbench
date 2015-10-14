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
use PhpBench\Dom\Document;
use PhpBench\Tabular\Tabular;
use PhpBench\Report\Renderer\ConsoleRenderer;

class TraceCommand extends BaseReportCommand
{
    private $launcher;
    private $builder;

    public function __construct(
        Launcher $launcher,
        CollectionBuilder $builder,
        Tabular $tabular
    ) {
        parent::__construct();
        $this->launcher = $launcher;
        $this->builder = $builder;
        $this->tabular = $tabular;
    }

    public function configure()
    {
        parent::configure();
        $this->setName('xdebug:trace');
        $this->setDescription('Generate a trace report');
        $this->addArgument('benchmark', InputArgument::REQUIRED, 'Path to the benchmark');
        $this->addArgument('subject', InputArgument::REQUIRED, 'The subject name');
        $this->addOption('dir', null, InputOption::VALUE_REQUIRED, 'Directory in which to dump the profile', getcwd());
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
            $name = str_replace('\\', '_', $benchmark->getClass()) . '::' . $subject->getName() . '.' . $index . '.trace';
            $path = $dir . '/' . $name;

            $this->launcher->launch(__DIR__ . '/../../../Benchmark/Remote/template/runner.template', array(
                'class' => $benchmark->getClass(),
                'file' => $benchmark->getPath(),
                'subject' => $subject->getName(),
                'revolutions' => 1,
                'beforeMethods' => var_export($subject->getBeforeMethods(), true),
                'afterMethods' => var_export($subject->getAfterMethods(), true),
                'parameters' => var_export($parameterSet, true),
            ), array(
                'xdebug.collect_params' => '3',
                'xdebug.trace_output_name' => $name,
                'xdebug.trace_output_dir' => $dir,
                'xdebug.trace_format' => '1',
                'xdebug.auto_trace' => '1',
            ));

            $dom = $this->traceToXml($path . '.xt');
        }

        $tableDom = $this->tabular->tabulate($dom, __DIR__ . '/../reports/trace.json', array(
            'basepath' => getcwd() . '/',
            'bench_subject' => $subject->getName()

        ));
        $reportDom = new Document();
        $reportEl = $reportDom->createRoot('reports');
        $reportEl = $reportEl->appendElement('report');
        $tableEl = $reportEl->ownerDocument->importNode($tableDom->firstChild, true);
        $reportEl->appendChild($tableEl);
        $renderer = new ConsoleRenderer();
        $renderer->setOutput($output);
        $renderer->render($reportDom, $renderer->getDefaultConfig());
    }

    private function traceToXml($path)
    {
        $dom = new Document(1.0);
        $traceEl = $dom->createRoot('trace');
        $handle = fopen($path, 'r');
        $version = fgets($handle);

        if (!preg_match('/^Version: (.*)$/', $version, $matches)) {
            throw new \InvalidArgumentException(sprintf(
                'Could not parse trace file "%s"',
                $path
            ));
        }

        $version = $matches[1];
        $traceEl->setAttribute('version', $matches[1]);

        $format = fgets($handle);
        preg_match('/^File format: (.*)$/', $format, $matches);
        $traceEl->setAttribute('format', $matches[1]);

        $start = fgets($handle);
        preg_match('/^TRACE START \[(.*)\]$/', $start, $matches);
        $traceEl->setAttribute('start', $matches[1]);
        $scopeEl = $traceEl;

        $tree = array();

        while ($line = fgets($handle)) {
            if (preg_match('/TRACE END\s+\[(.*)\]/', $line, $matches)) {
                $scopeEl->setAttribute('end', $matches[1]);
                break;
            }
            $parts = explode("\t", $line);
            $level = $parts[0];

            // '0' is entry, '1' is exit, 'R' is return
            if ($parts[2] == '1') {
                $scopeEl = $tree[$level];
                $scopeEl->setAttribute('end', 'hello');
                $scopeEl->setAttribute('end-time', $parts[3]);
                $scopeEl->setAttribute('end-memory', $parts[4]);
                $scopeEl = $scopeEl->parentNode;
                continue;
            } elseif ($parts[2] == 'R') {
                $scopeEl = $tree[$level];
                $scopeEl = $scopeEl->parentNode;
                continue;
            } elseif ($parts[2] == '') {
                continue;
            }

            $entryEl = $scopeEl->appendElement('entry');
            $entryEl->setAttribute('level', $parts[0]);
            $entryEl->setAttribute('func_nb', $parts[1]);
            $entryEl->setAttribute('time', $parts[3]);
            $entryEl->setAttribute('memory', $parts[4]);
            $entryEl->setAttribute('function', $parts[5]);
            $entryEl->setAttribute('is_user', $parts[6]);
            $entryEl->setAttribute('include', $parts[7]);
            $entryEl->setAttribute('filename', $parts[8]);
            $entryEl->setAttribute('line', $parts[9]);
            $entryEl->setAttribute('nb_params', $parts[10]);

            $tree[$level] = $entryEl;
            $scopeEl = $entryEl;
        }

        return $dom;
    }
}
