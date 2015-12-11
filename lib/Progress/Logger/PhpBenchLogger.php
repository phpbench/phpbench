<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Progress\Logger;

use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Console\OutputAwareInterface;
use PhpBench\PhpBench;
use Symfony\Component\Console\Output\OutputInterface;

class PhpBenchLogger extends NullLogger implements OutputAwareInterface
{
    protected $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function startSuite(SuiteDocument $suiteDocument)
    {
        $this->output->writeln('PhpBench ' . PhpBench::VERSION . '. Running benchmarks.');

        if ($configPath = $suiteDocument->firstChild->firstChild->getAttribute('config-path')) {
            $this->output->writeln(sprintf('Using configuration file: %s', $configPath));
        }

        $this->output->writeln('');
    }

    public function endSuite(SuiteDocument $suiteDocument)
    {
        $this->output->writeln(sprintf(
            '%s subjects, %s iterations, %s revs, %s rejects',
            $suiteDocument->getNbSubjects(),
            $suiteDocument->getNbIterations(),
            $suiteDocument->getNbRevolutions(),
            $suiteDocument->getNbRejects()
        ));
        $this->output->writeln(sprintf(
            'min mean max: %s %s %s (μs/r)',
            number_format($suiteDocument->getMin(), 2),
            number_format($suiteDocument->getMeanTime(), 2),
            number_format($suiteDocument->getMax(), 2)
        ));
        $this->output->writeln(sprintf(
            '⅀T: %sμs μSD/r %sμs μRSD/r: %s%%',
            $suiteDocument->getTotalTime(),
            number_format($suiteDocument->getMeanStDev(), 2),
            number_format($suiteDocument->getMeanRelStDev(), 2)
        ));
    }
}
