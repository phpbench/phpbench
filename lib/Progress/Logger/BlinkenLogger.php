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

use PhpBench\Benchmark\Iteration;
use PhpBench\Benchmark\IterationCollection;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\SuiteDocument;
use PhpBench\Util\TimeUnit;

class BlinkenLogger extends PhpBenchLogger
{
    /**
     * Number of measurements to show per row.
     */
    const NUMBER_COLS = 15;

    private $rejects = array();
    private $depth = 0;

    /**
     * {@inheritdoc}
     */
    public function benchmarkStart(BenchmarkMetadata $benchmark)
    {
        static $first = true;

        if (false === $first) {
            $this->output->write(PHP_EOL);
        }
        $first = false;
        $this->output->write(sprintf('<comment>%s</comment>', $benchmark->getClass()));

        $subjectNames = array();
        foreach ($benchmark->getSubjectMetadatas() as $subject) {
            $subjectNames[] = sprintf('#%s %s', $subject->getIndex(), $subject->getName());
        }

        $this->output->write(sprintf(' (%s)', implode(', ', $subjectNames)));
        $this->output->write(PHP_EOL);
        $this->output->write(PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function iterationStart(Iteration $iteration)
    {
        $this->drawIterations($iteration->getCollection(), $this->rejects, 'error', $iteration->getIndex());
        $this->output->write(PHP_EOL);
        $this->output->write("\x1B[0J"); // clear the line
        $this->output->write(PHP_EOL);
        $this->output->write(sprintf(
            '<info>subject</info> %s<info> with </info>%s<info> iteration(s) of </info>%s<info> rev(s),</info>',
            sprintf('%s', $iteration->getCollection()->getSubject()->getName()),
            $iteration->getCollection()->count(),
            $iteration->getRevolutions()
        ));
        $this->output->write(PHP_EOL);
        $this->output->write(sprintf(
            '<info>parameters</info> %s',
            json_encode($iteration->getCollection()->getParameterSet()->getArrayCopy(), true)
        ));
        $this->output->write("\x1B[". ($this->depth + 3) . 'A'); // put the cursor back to the line with the measurements
        $this->output->write("\x1B[0G"); // put the cursor back at col 0
    }

    /**
     * {@inheritdoc}
     */
    public function iterationsEnd(IterationCollection $iterations)
    {
        // make all numbers white
        $this->drawIterations($iterations, array(), null);

        if ($iterations->hasException()) {
            $this->output->write(' <error>ERROR</error>');
            $this->output->write("\x1B[0J"); // clear the rest of the line
            $this->output->write(PHP_EOL);

            return;
        }

        $this->rejects = array();
        foreach ($iterations->getRejects() as $reject) {
            $this->rejects[$reject->getIndex()] = true;
        }

        if ($iterations->getRejectCount() > 0) {
            if ($this->depth) {
                $this->output->write("\x1B[". ($this->depth) . 'A'); // put the cursor back to the line with the measurements
            }
            $this->output->write("\x1B[0G"); // put the cursor back at col 0
            return;
        }

        $mode = $iterations->getSubject()->getOutputMode();
        $timeUnit = $iterations->getSubject()->getOutputTimeUnit();
        $stats = $iterations->getStats();
        $this->output->write(sprintf(
            '<comment> [μ Mo]/r: %s %s (%s) μRSD/r: %s%%</comment>',
            $this->timeUnit->format($stats['mean'], $this->timeUnit->resolveDestUnit($timeUnit), $this->timeUnit->resolveMode($mode), null, false),
            $this->timeUnit->format($stats['mode'], $this->timeUnit->resolveDestUnit($timeUnit), $this->timeUnit->resolveMode($mode), null, false),
            $this->timeUnit->getDestSuffix($this->timeUnit->resolveDestUnit($timeUnit)),
            number_format($stats['rstdev'], 2)
        ));

        $this->output->write(PHP_EOL);
    }

    private function drawIterations(IterationCollection $collection, $specials, $tag, $current = null)
    {
        $subject = $collection->getSubject();
        $timeUnit = $subject->getOutputTimeUnit();
        $outputMode = $subject->getOutputMode();
        $this->output->write("\x1B[0G"); // put cursor at column 0
        $this->output->write(sprintf('#%-2s', $subject->getIndex()));

        $padding = 1;
        $depth = 0;
        for ($index = 0; $index < $collection->count(); $index++) {
            $otherIteration = $collection->getIteration($index);

            $time = 0;
            if ($otherIteration->hasResult()) {
                $time = $otherIteration->getResult()->getTime() / $otherIteration->getRevolutions();
            }

            $displayTime = number_format(
                $this->timeUnit->toDestUnit(
                    $time,
                    $this->timeUnit->resolveDestUnit($timeUnit),
                    $this->timeUnit->resolveMode($outputMode)
                ),
                $this->timeUnit->getPrecision()
            );

            if (strlen($displayTime) > $padding) {
                $padding = strlen($displayTime);
            }

            $output = sprintf('%' . ($padding + 2) . 's', $displayTime);

            if ($current === $index) {
                $output = sprintf('<bg=green>%s</>', $output);
            } elseif (isset($specials[$otherIteration->getIndex()])) {
                $output = sprintf('<%s>%s</%s>', $tag, $output, $tag);
            }

            $this->output->write($output);
            if ($index > 0 && ($index + 1) % self::NUMBER_COLS == 0) {
                $depth++;
                $this->output->write(PHP_EOL);
                $this->output->write('   ');
            }
        }

        $this->depth = $depth;

        $this->output->write(sprintf(
            ' (%s)',
            $this->timeUnit->getDestSuffix(
                $this->timeUnit->resolveDestUnit($timeUnit),
                $this->timeUnit->resolveMode($outputMode)
            )
        ));
        $this->output->write("\x1B[0J"); // clear rest of the line
    }

    public function endSuite(SuiteDocument $suiteDocument)
    {
        $this->output->write(PHP_EOL);
        parent::endSuite($suiteDocument);
    }
}
