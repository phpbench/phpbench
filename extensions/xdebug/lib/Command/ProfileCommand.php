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
use PhpBench\Extensions\XDebug\XDebugUtil;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class ProfileCommand extends Command
{
    /**
     * @var RunnerHandler
     */
    private $runnerHandler;
    /**
     * @var OutputDirHandler
     */
    private $outputDirHandler;
    /**
     * @var XDebugUtil
     */
    private $xdebugUtil;

    public function __construct(
        RunnerHandler $runnerHandler,
        OutputDirHandler $outputDirHandler,
        XDebugUtil $xdebugUtil
    ) {
        parent::__construct();
        $this->runnerHandler = $runnerHandler;
        $this->outputDirHandler = $outputDirHandler;
        $this->xdebugUtil = $xdebugUtil;
    }

    public function configure(): void
    {
        $this->setName('xdebug:profile');
        $this->setDescription(
            <<<'EOT'
Generate and optionally visualize profiles with Xdebug
EOT
        );
        RunnerHandler::configure($this);
        OutputDirHandler::configure($this);

        $this->addOption('gui', null, InputOption::VALUE_NONE);
        $this->addOption('gui-bin', null, InputOption::VALUE_REQUIRED, 'Bin to use to display cachegrind output', 'kcachegrind');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $outputDir = $this->outputDirHandler->handleOutputDir($input, $output);
        $guiBin = null;

        if ($input->getOption('gui')) {
            $finder = new ExecutableFinder();
            $guiBin = $finder->find($input->getOption('gui-bin'));

            if (null === $guiBin) {
                throw new \InvalidArgumentException(sprintf(
                    'Could not locate GUI bin "%s"',
                    $input->getOption('gui-bin')
                ));
            }
        }

        $generatedFiles = [];
        $config = RunnerConfig::create()
            ->withExecutor([
                'executor' => 'xdebug_profile',
                'output_dir' => $outputDir,
                'callback' => function ($iteration) use ($outputDir, $guiBin, &$generatedFiles): void {
                    $generatedFiles[] = $generatedFile = $outputDir . DIRECTORY_SEPARATOR . $this->xdebugUtil->filenameFromContext($iteration, $this->xdebugUtil->getCachegrindExtensionOfGeneratedFile());

                    if ($guiBin) {
                        $process = Process::fromShellCommandline(sprintf(
                            $guiBin . ' ' . $generatedFile
                        ));
                        $process->run();
                    }
                },
            ])
            ->withIterations([1]);
        $this->runnerHandler->runFromInput($input, $output, $config);

        $output->write(PHP_EOL);
        $output->writeln(sprintf(
            '<info>%s profile(s) generated:</info>',
            count($generatedFiles)
        ));
        $output->write(PHP_EOL);

        foreach ($generatedFiles as $generatedFile) {
            if (!file_exists($generatedFile)) {
                throw new \InvalidArgumentException(sprintf(
                    'Profile "%s" was not generated. Perhaps you do not have Xdebug installed or the extension is not enabled for your benchmark?',
                    $generatedFile
                ));
            }

            $output->writeln(sprintf('    %s', $generatedFile));
        }

        return 0;
    }
}
