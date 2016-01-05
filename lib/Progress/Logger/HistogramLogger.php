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
use PhpBench\Math\Statistics;
use PhpBench\Util\TimeUnit;

class HistogramLogger extends AnsiLogger
{
    private $rows = 1;
    private $blocks = array('▁',  '▂',  '▃',  '▄',  '▅',  '▆', '▇', '█');

    /**
     * {@inheritdoc}
     */
    public function endSuite(SuiteDocument $suiteDocument)
    {
        $this->output->write(PHP_EOL);
        parent::endSuite($suiteDocument);
    }

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
        $this->output->write("\x1B[2K");
        $this->output->write(PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function iterationsStart(IterationCollection $iterations)
    {
        $this->drawIterations($iterations);
        $this->output->write("\x1B[1A"); // move cursor up
        $this->output->write(PHP_EOL);
        $this->renderCollectionStatus($iterations);
        $this->output->write("\x1B[1A"); // move cursor up
    }

    /**
     * {@inheritdoc}
     */
    public function iterationsEnd(IterationCollection $iterations)
    {
        $this->drawIterations($iterations);

        if ($iterations->hasException()) {
            $this->output->write(' <error>ERROR</error>');
            $this->output->write("\x1B[0J"); // clear the rest of the line
            $this->output->write(PHP_EOL);

            return;
        }

        if ($iterations->getRejectCount() > 0) {
            $this->output->write("\x1B[1A"); // move cursor up
            $this->output->write("\x1B[0G");

            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function iterationStart(Iteration $iteration)
    {
        $this->output->write(PHP_EOL);
        $this->output->write(PHP_EOL);
        $this->output->write(sprintf(
            '<info>it</info>%3d/%-3d<info> (rej </info>%s<info>)</info>',
            $iteration->getIndex(),
            $iteration->getCollection()->count(),
            $iteration->getCollection()->getRejectCount()
        ));
        $this->output->write("\x1B[2A");
        $this->output->write("\x1B[0G");
    }

    private function drawBlocks($freqs)
    {
        $steps = 7;
        $resolution = $this->rows * $steps;
        $max = max($freqs);
        $blocks = array();

        for ($row = 1; $row <= $this->rows; $row++) {
            $blocks[$row] = array();

            foreach ($freqs as &$freq) {
                if (null === $freq || 0 === $freq) {
                    $blocks[$row][] = ' ';
                    continue;
                }

                $scale = 1 / $max * $freq;
                $value = $resolution * $scale;

                $lowerLimit = $resolution - ($steps * $row);
                $upperLimit = $lowerLimit + $steps;

                if ($value >= $lowerLimit && $value < $upperLimit) {
                    $blockIndex = $value % $steps;

                    $blocks[$row][] = $this->blocks[$blockIndex];
                } elseif ($value < $lowerLimit) {
                    $blocks[$row][] = ' ';
                } else {
                    $blocks[$row][] = $this->blocks[7];
                }
            }
        }

        $output = array();
        foreach ($blocks as $blockRow) {
            $output[] = implode('', $blockRow);
        }

        $output = implode(sprintf(
            "\x1B[%sD\x1B[1B",
            count($blocks[1])
        ), $output);

        $this->output->write($output);
    }

    private function drawIterations(IterationCollection $iterations)
    {
        $subject = $iterations->getSubject();
        $this->output->write("\x1B[2K"); // clear the whole line
        $this->output->write(PHP_EOL);
        $this->output->write("\x1B[2K"); // clear the whole line
        $this->output->write("\x1B[1A");

        $sigma = 2;
        $bins = 16;

        if ($iterations->isComputed()) {
            $times = $iterations->getZValues();
            $stats = $iterations->getStats();
            $freqs = Statistics::histogram($times, $bins, -$sigma, $sigma);
        } else {
            $stats = array('stdev' => 0, 'mean' => 0, 'max' => 0);
            $freqs = array_fill(0, $bins + 1, null);
        }

        $this->output->write(sprintf(
            '#%-2d (σ = %s ) -%sσ [',
            $subject->getIndex(),
            $this->timeUnit->format($stats['stdev']),
            $sigma
        ));
        $this->drawBlocks($freqs, $stats);

        $this->output->write(sprintf(
            '] +%sσ <comment>%s</comment>',
            $sigma,
            $iterations->isComputed() ? $this->formatIterationsShortSummary($iterations) : ''
        ));

        $this->output->write(PHP_EOL);
    }
}
