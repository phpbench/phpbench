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
use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Output\OutputInterface;

class PhpBenchLogger extends NullLogger implements OutputAwareInterface
{
    protected $output;
    protected $timeUnit;

    public function __construct(TimeUnit $timeUnit = null)
    {
        $this->timeUnit = $timeUnit;
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
            number_format($this->timeUnit->value($suiteDocument->getMin()), 3),
            number_format($this->timeUnit->value($suiteDocument->getMeanTime()), 3),
            number_format($this->timeUnit->value($suiteDocument->getMax()), 3),
            $this->timeUnit->getDestSuffix()
        ));
        $this->output->writeln(sprintf(
            '⅀T: %s%s μSD/r %s%s μRSD/r: %s%%',
            number_format($this->timeUnit->value($suiteDocument->getTotalTime()), 3),
            $this->timeUnit->getDestSuffix(),
            number_format($this->timeUnit->value($suiteDocument->getMeanStDev()), 3),
            $this->timeUnit->getDestSuffix(),
            number_format($suiteDocument->getMeanRelStDev(), 3)
        ));
    }
}
