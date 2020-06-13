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

namespace PhpBench\Extensions\XDebug\Command;

use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Console\Command\Handler\RunnerHandler;
use PhpBench\Extensions\XDebug\Command\Handler\OutputDirHandler;
use PhpBench\Extensions\XDebug\Renderer\TraceRenderer;
use PhpBench\Extensions\XDebug\Result\XDebugTraceResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TraceCommand extends Command
{
    private $runnerHandler;
    private $renderer;
    private $outputDirHandler;

    public function __construct(
        RunnerHandler $runnerHandler,
        TraceRenderer $renderer,
        OutputDirHandler $outputDirHandler
    ) {
        parent::__construct();
        $this->runnerHandler = $runnerHandler;
        $this->renderer = $renderer;
        $this->outputDirHandler = $outputDirHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('xdebug:trace');
        $this->setDescription(<<<'EOT'
Generate and optionally visualize traces with Xdebug
EOT
        );
        RunnerHandler::configure($this);
        OutputDirHandler::configure($this);
        $this->addOption('dump', null, InputOption::VALUE_NONE, 'Dump the raw trace XML');
        $this->addOption('no-benchmark-filter', null, InputOption::VALUE_NONE, 'Do not filter functions surrounding the benchmark');
        $this->addOption('trace-filter', null, InputOption::VALUE_REQUIRED, 'Regex function name filter');
        $this->addOption('show-args', null, InputOption::VALUE_NONE, 'Show function arguments');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $outputDir = $this->outputDirHandler->handleOutputDir($input, $output);
        $this->output = $output;
        $dump = $input->getOption('dump');

        $config = RunnerConfig::create()
            ->withExecutor([
                'executor' => 'xdebug_trace',
                'output_dir' => $outputDir,
            ])
            ->withIterations([1]);
        $suite = $this->runnerHandler->runFromInput($input, $output, $config);

        if ($dump) {
            foreach ($suite->getIterations() as $iteration) {
                $this->renderDump($iteration, $output);
            }

            return 0;
        }

        $this->renderer->render($suite, $output, [
            'filter_benchmark' => !$input->getOption('no-benchmark-filter'),
            'show_args' => $input->getOption('show-args'),
            'filter' => $input->getOption('trace-filter'),
        ]);

        return 0;
    }

    private function renderDump($iteration, $output)
    {
        $result = $iteration->getResult(XDebugTraceResult::class);
        $trace = $result->getTraceDocument();
        $output->write($trace->dump());
    }
}
