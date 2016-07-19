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

namespace PhpBench\Extensions\XDebug\Command\Handler;

use PhpBench\PhpBench;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class OutputDirHandler
{
    private $outputDir;
    private $filesystem;

    public function __construct($outputDir, Filesystem $filesystem = null)
    {
        $this->outputDir = $outputDir;
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    public static function configure(Command $command)
    {
        $command->addOption('outdir', null, InputOption::VALUE_REQUIRED, 'Output directory');
    }

    public function handleOutputDir(InputInterface $input, OutputInterface $output)
    {
        $outputDir = PhpBench::normalizePath($input->getOption('outdir') ?: $this->outputDir);

        if (!$this->filesystem->exists($outputDir)) {
            $output->writeln(sprintf(
                '<comment>// creating non-existing directory %s</comment>',
                $outputDir
            ));
            $this->filesystem->mkdir($outputDir);
        }

        return $outputDir;
    }
}
