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

namespace PhpBench\Progress\Logger;

use PhpBench\Assertion\AssertionFailures;
use PhpBench\Assertion\AssertionWarnings;
use PhpBench\Console\OutputAwareInterface;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;
use PhpBench\PhpBench;
use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Output\OutputInterface;

abstract class PhpBenchLogger extends NullLogger implements OutputAwareInterface
{
    protected $output;

    /**
     * @var TimeUnit
     */
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
        $this->output->writeln('PHPBench ' . PhpBench::VERSION . ' running benchmarks...');

        if ($configPath = $suite->getConfigPath()) {
            $this->output->writeln(sprintf('with configuration file: %s', $configPath));
        }

        $summary = $suite->getSummary();
        $this->output->writeln(sprintf(
            'with PHP version %s, xdebug %s, opcache %s',
            $summary->getPhpVersion() ?? '<unknown>',
            $summary->getXdebugEnabled() ? '✔' : '❌',
            $summary->getOpcacheEnabled()  ? '✔' : '❌'
        ));

        $this->output->writeln('');
    }

    public function endSuite(Suite $suite)
    {
        $summary = $suite->getSummary();

        $this->listErrors($suite);
        $this->listFailures($suite);
        $this->listWarnings($suite);


        $this->output->writeln(sprintf(
            '%s subjects, %s iterations, %s revs, %s rejects, %s failures, %s warnings',
            number_format($summary->getNbSubjects()),
            number_format($summary->getNbIterations()),
            number_format($summary->getNbRevolutions()),
            number_format($summary->getNbRejects()),
            number_format($summary->getNbFailures()),
            number_format($summary->getNbWarnings())
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

    private function listErrors(Suite $suite)
    {
        $errorStacks = $suite->getErrorStacks();

        if (empty($errorStacks)) {
            return;
        }

        $this->output->write(PHP_EOL);
        $this->output->writeln(sprintf('%d subjects encountered errors:', count($errorStacks)));
        $this->output->write(PHP_EOL);

        foreach ($errorStacks as $errorStack) {
            $this->output->writeln(sprintf(
                '%s::%s</error>',
                $errorStack->getVariant()->getSubject()->getBenchmark()->getClass(),
                $errorStack->getVariant()->getSubject()->getName()
            ));
            $this->output->write(PHP_EOL);

            foreach ($errorStack as $error) {
                $this->output->writeln(sprintf(
                    "    %s %s\n\n    %s</comment>\n",
                    $error->getClass(),
                    str_replace("\n", "\n    ", $error->getMessage()),
                    str_replace("\n", "\n    ", $error->getTrace())
                ));
            }
        }
    }

    private function listFailures(Suite $suite)
    {
        $variantFailures = $suite->getFailures();

        if (empty($variantFailures)) {
            return;
        }

        $this->output->write(PHP_EOL);
        $this->output->writeln(sprintf('%d variants failed:', count($variantFailures)));
        $this->output->write(PHP_EOL);

        /** @var AssertionFailures $variantFailure */
        foreach ($variantFailures as $variantFailure) {
            $this->output->writeln(sprintf(
                '<error>%s::%s %s</error>',
                $variantFailure->getVariant()->getSubject()->getBenchmark()->getClass(),
                $variantFailure->getVariant()->getSubject()->getName(),
                json_encode($variantFailure->getVariant()->getParameterSet()->getArrayCopy())
            ));
            $this->output->write(PHP_EOL);

            foreach ($variantFailure as $index => $failure) {
                $this->output->writeln(sprintf('    %s) %s', $index + 1, $failure->getMessage()));
            }
            $this->output->write(PHP_EOL);
        }
    }

    private function listWarnings(Suite $suite)
    {
        $variantWarnings = $suite->getWarnings();

        if (empty($variantWarnings)) {
            return;
        }

        $this->output->write(PHP_EOL);
        $this->output->writeln(sprintf('%d variants have warnings:', count($variantWarnings)));
        $this->output->write(PHP_EOL);

        /** @var AssertionWarnings $variantWarning */
        foreach ($variantWarnings as $variantWarning) {
            $this->output->writeln(sprintf(
                '<warning>%s::%s %s</warning>',
                $variantWarning->getVariant()->getSubject()->getBenchmark()->getClass(),
                $variantWarning->getVariant()->getSubject()->getName(),
                json_encode($variantWarning->getVariant()->getParameterSet()->getArrayCopy())
            ));
            $this->output->write(PHP_EOL);

            foreach ($variantWarning as $index => $warning) {
                $this->output->writeln(sprintf('    %s) %s', $index + 1, $warning->getMessage()));
            }
            $this->output->write(PHP_EOL);
        }
    }

    public function formatIterationsFullSummary(Variant $variant)
    {
        $subject = $variant->getSubject();
        $stats = $variant->getStats();
        $timeUnit = $this->timeUnit->resolveDestUnit($variant->getSubject()->getOutputTimeUnit());
        $mode = $this->timeUnit->resolveMode($subject->getOutputMode());
        $precision = $this->timeUnit->resolvePrecision($subject->getOutputTimePrecision());

        return sprintf(
            "%s[μ Mo]/r: %s %s (%s) [μSD μRSD]/r: %s %s%%%s",

            $variant->hasFailed() ? '<error>' : '',
            $this->timeUnit->format($stats->getMean(), $timeUnit, $mode, $precision, false),
            $this->timeUnit->format($stats->getMode(), $timeUnit, $mode, $precision, false),
            $this->timeUnit->getDestSuffix($timeUnit, $mode),
            $this->timeUnit->format($stats->getStdev(), $timeUnit, TimeUnit::MODE_TIME),
            number_format($stats->getRstdev(), 2),
            $variant->hasFailed() ? '</error>' : ''
        );
    }

    public function formatIterationsShortSummary(Variant $variant)
    {
        $subject = $variant->getSubject();
        $stats = $variant->getStats();
        $timeUnit = $this->timeUnit->resolveDestUnit($variant->getSubject()->getOutputTimeUnit());
        $mode = $this->timeUnit->resolveMode($subject->getOutputMode());
        $precision = $this->timeUnit->resolvePrecision($subject->getOutputTimePrecision());

        return sprintf(
            '[μ Mo]/r: %s %s μRSD/r: %s%%',

            $this->timeUnit->format($stats->getMean(), $timeUnit, $mode, $precision, false),
            $this->timeUnit->format($stats->getMode(), $timeUnit, $mode, $precision, false),
            number_format($stats->getRstdev(), 2)
        );
    }

    protected function formatIterationTime(Iteration $iteration)
    {
        $subject = $iteration->getVariant()->getSubject();
        $timeUnit = $subject->getOutputTimeUnit();
        $outputMode = $subject->getOutputMode();

        $time = 0;

        if ($iteration->hasResult(TimeResult::class)) {
            $timeResult = $iteration->getResult(TimeResult::class);
            assert($timeResult instanceof TimeResult);
            $time = $timeResult->getRevTime($iteration->getVariant()->getRevolutions());
        }

        return number_format(
            $this->timeUnit->toDestUnit(
                $time,
                $this->timeUnit->resolveDestUnit($timeUnit),
                $this->timeUnit->resolveMode($outputMode)
            ),
            $this->timeUnit->resolvePrecision($subject->getOutputTimePrecision())
        );
    }

    protected function formatVariantName(Variant $variant)
    {
        if (count($variant->getSubject()->getVariants()) > 1) {
            return sprintf(
                '%s # %s',
                $variant->getSubject()->getName(),
                $variant->getParameterSet()->getName()
            );
        }

        return $variant->getSubject()->getName();
    }
}
