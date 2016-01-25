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

use PhpBench\Console\Command\Configure\Executor;
use PhpBench\Console\Command\Handler\RunnerHandler;
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
    private $output;

    public function __construct(
        RunnerHandler $runnerHandler,
        Filesystem $filesystem = null
    ) {
        parent::__construct();
        $this->runnerHandler = $runnerHandler;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public function configure()
    {
        $this->setName('xdebug:profile');
        $this->setDescription(<<<'EOT'
Generate and optionally visulaize profiles with XDebug
EOT
        );
        RunnerHandler::configure($this);
        $this->addOption('outdir', null, InputOption::VALUE_REQUIRED, 'Output directory for profiles', 'profile');
        $this->addOption('gui', null, InputOption::VALUE_NONE);
        $this->addOption('gui-bin', null, InputOption::VALUE_REQUIRED, 'Bin to use to display cachegrind output', 'kcachegrind');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $outputDir = $input->getOption('outdir');

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

        if (!$this->filesystem->exists($outputDir)) {
            $output->writeln(sprintf(
                '<comment>// creating non-existing directory %s</comment>',
                $outputDir
            ));
            $this->filesystem->mkdir($outputDir);
        }

        $generatedFiles = array();
        $this->runnerHandler->runFromInput($input, $output, array(
            'executor' => array(
                'executor' => 'xdebug',
                'output_dir' => $outputDir,
                'callback' => function ($iteration) use ($outputDir, $guiBin, &$generatedFiles) {
                    $generatedFiles[] = $generatedFile = $outputDir . DIRECTORY_SEPARATOR . XDebugUtil::filenameFromIteration($iteration);

                    if ($guiBin) {
                        $process = new Process(sprintf(
                            $guiBin . ' ' . $generatedFile
                        ));
                        $process->run();
                    }
                },
            ),
            'iterations' => 1,
        ));

        $output->write(PHP_EOL);
        $output->writeln(sprintf(
            '<info>%s profile(s) generated:</info>',
            count($generatedFiles)
        ));
        $output->write(PHP_EOL);

        foreach ($generatedFiles as $generatedFile) {
            if (!file_exists($generatedFile)) {
                throw new \InvalidArgumentException(sprintf(
                    'Profile "%s" was not generated. Maybe you do not have XDebug installed?',
                    $generatedFile
                ));
            }

            $this->output->writeln(sprintf('    %s', $generatedFile));
        }
    }
}
