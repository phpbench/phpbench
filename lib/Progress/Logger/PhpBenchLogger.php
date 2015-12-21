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
use PhpBench\Util\TimeFormatter;
use Symfony\Component\Console\Output\OutputInterface;

class PhpBenchLogger extends NullLogger implements OutputAwareInterface
{
    protected $output;
    protected $timeFormatter;

    public function __construct(TimeFormatter $timeFormatter = null)
    {
        $this->timeFormatter = $timeFormatter;
    }

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
            'min mean max: %s %s %s (%s/r)',
            number_format($this->timeFormatter->convert($suiteDocument->getMin()), 3),
            number_format($this->timeFormatter->convert($suiteDocument->getMeanTime()), 3),
            number_format($this->timeFormatter->convert($suiteDocument->getMax()), 3),
            $this->timeFormatter->getDestSuffix()
        ));
        $this->output->writeln(sprintf(
            '⅀T: %s μSD/r %s%s μRSD/r: %s%%',
            $this->timeFormatter->format($suiteDocument->getTotalTime()),
            $this->timeFormatter->format($suiteDocument->getMeanStDev(), TimeFormatter::MODE_TIME),
            $this->timeFormatter->getDestSuffix(),
            number_format($suiteDocument->getMeanRelStDev(), 3)
        ));
    }
}
