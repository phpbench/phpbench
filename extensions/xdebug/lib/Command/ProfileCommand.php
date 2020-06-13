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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class ProfileCommand extends Command
{
    private $runnerHandler;
    private $filesystem;
    private $outputDirHandler;

    public function __construct(
        RunnerHandler $runnerHandler,
        OutputDirHandler $outputDirHandler,
        Filesystem $filesystem = null
    ) {
        parent::__construct();
        $this->runnerHandler = $runnerHandler;
        $this->filesystem = $filesystem ?: new Filesystem();
        $this->outputDirHandler = $outputDirHandler;
    }

    public function configure()
    {
        $this->setName('xdebug:profile');
        $this->setDescription(<<<'EOT'
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
                    'Could not locate GUI bin "%s"', $input->getOption('gui-bin')
                ));
            }
        }

        $generatedFiles = [];
        $config = RunnerConfig::create()
            ->withExecutor([
                'executor' => 'xdebug_profile',
                'output_dir' => $outputDir,
                'callback' => function ($iteration) use ($outputDir, $guiBin, &$generatedFiles) {
                    $generatedFiles[] = $generatedFile = $outputDir . DIRECTORY_SEPARATOR . XDebugUtil::filenameFromIteration($iteration, '.cachegrind');

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
                    'Profile "%s" was not generated. Maybe you do not have Xdebug installed?',
                    $generatedFile
                ));
            }

            $output->writeln(sprintf('    %s', $generatedFile));
        }

        return 0;
    }
}
