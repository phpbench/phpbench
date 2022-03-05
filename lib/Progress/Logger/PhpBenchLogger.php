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

use PhpBench\Benchmark\RunnerConfig;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Suite;
use PhpBench\Model\Summary;
use PhpBench\Model\Variant;
use PhpBench\PhpBench;
use PhpBench\Progress\VariantFormatter;
use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Output\OutputInterface;

abstract class PhpBenchLogger extends NullLogger
{
    /**
     * @var TimeUnit
     */
    protected $timeUnit;

    /**
     * @var OutputInterface
     */
    public $output;

    /**
     * @var VariantFormatter
     */
    private $formatter;

    public function __construct(
        OutputInterface $output,
        VariantFormatter $formatter,
        TimeUnit $timeUnit = null
    ) {
        $this->timeUnit = $timeUnit;
        $this->output = $output;
        $this->formatter = $formatter;
    }

    public function startSuite(RunnerConfig $config, Suite $suite): void
    {
        $this->output->writeln(sprintf(
            'PHPBench (%s) running benchmarks... <fg=cyan>#standwith</><fg=yellow>ukraine</>',
            PhpBench::version()
        ));

        if ($configPath = $suite->getConfigPath()) {
            $this->output->writeln(sprintf('with configuration file: %s', $configPath));
        }

        $summary = $suite->getSummary();
        $this->output->writeln(sprintf(
            'with PHP version %s, xdebug %s, opcache %s',
            $summary->getPhpVersion() ?? '<unknown>',
            $summary->getXdebugEnabled() ? '✔' : '❌',
            $summary->getOpcacheEnabled() ? '✔' : '❌'
        ));

        foreach ($config->getBaselines() as $baseline) {
            $this->output->writeln(sprintf(
                'comparing [%s vs. %s]',
                $suite->getTag() ?: 'actual',
                $baseline->getTag()
            ));
        }

        $this->output->writeln('');
    }

    public function endSuite(Suite $suite): void
    {
        $summary = $suite->getSummary();

        $this->listErrors($suite);
        $this->listFailures($suite);

        $this->output->writeln((function (Summary $summary, string $message) {
            if ($summary->getNbFailures() || $summary->getNbErrors()) {
                return sprintf('<error>%s</>', $message);
            }

            if ($summary->getNbAssertions()) {
                return sprintf('<success>%s</>', $message);
            }

            return $message;
        })($suite->getSummary(), sprintf(
            'Subjects: %s, Assertions: %s, Failures: %s, Errors: %s',
            number_format($summary->getNbSubjects()),
            number_format($summary->getNbAssertions()),
            number_format($summary->getNbFailures()),
            number_format($summary->getNbErrors())
        )));
    }

    private function listErrors(Suite $suite): void
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
                '<error>%s::%s</>',
                $errorStack->getVariant()->getSubject()->getBenchmark()->getClass(),
                $errorStack->getVariant()->getSubject()->getName()
            ));
            $this->output->write(PHP_EOL);

            foreach ($errorStack as $error) {
                $this->output->writeln(sprintf(
                    "    %s\n",
                    str_replace("\n", "\n    ", $error->getMessage())
                ));
            }
        }
    }

    private function listFailures(Suite $suite): void
    {
        $variantFailures = $suite->getFailures();

        if (empty($variantFailures)) {
            return;
        }

        $this->output->writeln(sprintf('%d variants failed:', count($variantFailures)));
        $this->output->write(PHP_EOL);

        foreach ($variantFailures as $variantFailure) {
            $this->output->writeln(sprintf(
                '  <fg=red>✘</> %s::%s # %s',
                $variantFailure->getVariant()->getSubject()->getBenchmark()->getClass(),
                $variantFailure->getVariant()->getSubject()->getName(),
                $variantFailure->getVariant()->getParameterSet()->getName()
            ));
            $this->output->write(PHP_EOL);

            foreach ($variantFailure as $index => $failure) {
                $this->output->writeln(sprintf('    %s) %s', $index + 1, str_replace("\n", "\n       ", $failure->getMessage())));
            }
            $this->output->write(PHP_EOL);
        }
    }

    public function formatIterationsFullSummary(Variant $variant): string
    {
        return $this->formatter->formatVariant($variant);
    }

    public function formatIterationsShortSummary(Variant $variant): string
    {
        return $this->formatter->formatVariant($variant);
    }

    protected function formatIterationTime(Iteration $iteration): string
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

    protected function formatVariantName(Variant $variant): string
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
