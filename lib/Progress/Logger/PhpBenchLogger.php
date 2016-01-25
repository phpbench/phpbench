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

use PhpBench\Console\OutputAwareInterface;
use PhpBench\Model\Iteration;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\PhpBench;
use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Output\OutputInterface;

abstract class PhpBenchLogger extends NullLogger implements OutputAwareInterface
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

    public function startSuite(Suite $suite)
    {
        $this->output->writeln('PhpBench ' . PhpBench::VERSION . '. Running benchmarks.');

        if ($configPath = $suite->getConfigPath()) {
            $this->output->writeln(sprintf('Using configuration file: %s', $configPath));
        }

        $this->output->writeln('');
    }

    public function endSuite(Suite $suite)
    {
        $summary = $suite->getSummary();
        $errorStacks = $suite->getErrorStacks();
        if ($errorStacks) {
            $this->output->write(PHP_EOL);
            $this->output->writeln(sprintf('%d subjects encountered errors:', count($errorStacks)));
            $this->output->write(PHP_EOL);
            foreach ($errorStacks as $errorStack) {
                $this->output->writeln(sprintf(
                    '<error>%s::%s</error>',
                    $errorStack->getVariant()->getSubject()->getBenchmark()->getClass(),
                    $errorStack->getVariant()->getSubject()->getName()
                ));
                $this->output->write(PHP_EOL);
                foreach ($errorStack as $error) {
                    $this->output->writeln(sprintf(
                        '    %s %s',
                        $error->getClass(),
                        str_replace("\n", "\n    ", $error->getMessage())
                    ));
                }
            }
        }

        $this->output->writeln(sprintf(
            '%s subjects, %s iterations, %s revs, %s rejects',
            $summary->getNbSubjects(),
            $summary->getNbIterations(),
            $summary->getNbRevolutions(),
            $summary->getNbRejects()
        ));

        $this->output->writeln(sprintf(
            '(best [mean mode] worst) = %s [%s %s] %s (%s)',
            number_format($this->timeUnit->toDestUnit($summary->getMinTime()), 3),
            number_format($this->timeUnit->toDestUnit($summary->getMeanTime()), 3),
            number_format($this->timeUnit->toDestUnit($summary->getModeTime()), 3),
            number_format($this->timeUnit->toDestUnit($summary->getMaxTime()), 3),
            $this->timeUnit->getDestSuffix()
        ));

        $this->output->writeln(sprintf(
            '⅀T: %s μSD/r %s μRSD/r: %s%%',
            $this->timeUnit->format($summary->getTotalTime(), null, TimeUnit::MODE_TIME),
            $this->timeUnit->format($summary->getMeanStDev(), null, TimeUnit::MODE_TIME),
            number_format($summary->getMeanRelStDev(), 3)
        ));
    }

    public function formatIterationsFullSummary(Variant $iterations)
    {
        $stats = $iterations->getStats();
        $timeUnit = $this->timeUnit->resolveDestUnit($iterations->getSubject()->getOutputTimeUnit());
        $mode = $this->timeUnit->resolveMode($iterations->getSubject()->getOutputMode());

        return sprintf(
            "[μ Mo]/r: %s %s (%s) \t[μSD μRSD]/r: %s %s%%",

            $this->timeUnit->format($stats->getMean(), $timeUnit, $mode, null, false),
            $this->timeUnit->format($stats->getMode(), $timeUnit, $mode, null, false),
            $this->timeUnit->getDestSuffix($timeUnit, $mode),
            $this->timeUnit->format($stats->getStdev(), $timeUnit, TimeUnit::MODE_TIME),
            number_format($stats->getRstdev(), 2)
        );
    }

    public function formatIterationsShortSummary(Variant $iterations)
    {
        $stats = $iterations->getStats();
        $timeUnit = $this->timeUnit->resolveDestUnit($iterations->getSubject()->getOutputTimeUnit());
        $mode = $this->timeUnit->resolveMode($iterations->getSubject()->getOutputMode());

        return sprintf(
            '[μ Mo]/r: %s %s μRSD/r: %s%%',

            $this->timeUnit->format($stats->getMean(), $timeUnit, $mode, null, false),
            $this->timeUnit->format($stats->getMode(), $timeUnit, $mode, null, false),
            number_format($stats->getRstdev(), 2)
        );
    }

    protected function formatIterationTime(Iteration $iteration)
    {
        $subject = $iteration->getSubject();
        $timeUnit = $subject->getOutputTimeUnit();
        $outputMode = $subject->getOutputMode();

        $time = 0;
        if (null !== $iteration->getTime()) {
            $time = $iteration->getTime() / $iteration->getRevolutions();
        }

        return number_format(
            $this->timeUnit->toDestUnit(
                $time,
                $this->timeUnit->resolveDestUnit($timeUnit),
                $this->timeUnit->resolveMode($outputMode)
            ),
            $this->timeUnit->getPrecision()
        );
    }
}
