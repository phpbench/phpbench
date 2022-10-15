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

use PhpBench\Path\Path;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class OutputDirHandler
{
    /**
     * @var string
     */
    private $outputDir;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $cwd;

    public function __construct(string $outputDir, string $cwd, Filesystem $filesystem = null)
    {
        $this->outputDir = $outputDir;
        $this->filesystem = $filesystem ?: new Filesystem();
        $this->cwd = $cwd;
    }

    public static function configure(Command $command): void
    {
        $command->addOption('outdir', null, InputOption::VALUE_REQUIRED, 'Output directory');
    }

    public function handleOutputDir(InputInterface $input, OutputInterface $output): string
    {
        $outputDir = Path::makeAbsolute($input->getOption('outdir') ?: $this->outputDir, $this->cwd);

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
