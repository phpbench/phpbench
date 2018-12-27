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

use PhpBench\Math\Distribution;
use PhpBench\Math\Statistics;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\ComputedResult;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;

class HistogramLogger extends AnsiLogger
{
    private $rows = 1;
    private $blocks = ['▁',  '▂',  '▃',  '▄',  '▅',  '▆', '▇', '█'];

    /**
     * {@inheritdoc}
     */
    public function endSuite(Suite $suite)
    {
        $this->output->write(PHP_EOL);
        parent::endSuite($suite);
    }

    /**
     * {@inheritdoc}
     */
    public function benchmarkStart(Benchmark $benchmark)
    {
        $this->output->write(PHP_EOL);
        $this->output->write(sprintf('<comment>%s</comment>', $benchmark->getClass()));
        $subjectNames = [];

        foreach ($benchmark->getSubjects() as $subject) {
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
    public function variantStart(Variant $variant)
    {
        $this->drawIterations($variant);
        $this->output->write("\x1B[1A"); // move cursor up
        $this->output->write(PHP_EOL);
        $this->renderCollectionStatus($variant);
        $this->output->write("\x1B[1A"); // move cursor up
    }

    /**
     * {@inheritdoc}
     */
    public function variantEnd(Variant $variant)
    {
        $this->drawIterations($variant);

        if ($variant->hasErrorStack()) {
            $this->output->write(' <error>ERROR</error>');
            $this->output->write("\x1B[0J"); // clear the rest of the line
            $this->output->write(PHP_EOL);

            return;
        }

        if ($variant->getRejectCount() > 0) {
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
            $iteration->getVariant()->count(),
            $iteration->getVariant()->getRejectCount()
        ));
        $this->output->write("\x1B[2A");
        $this->output->write("\x1B[0G");
    }

    private function drawBlocks($freqs)
    {
        $steps = 7;
        $resolution = $this->rows * $steps;
        $max = max($freqs);
        $blocks = [];

        for ($row = 1; $row <= $this->rows; $row++) {
            $blocks[$row] = [];

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

        $output = [];

        foreach ($blocks as $blockRow) {
            $output[] = implode('', $blockRow);
        }

        $output = implode(sprintf(
            "\x1B[%sD\x1B[1B",
            count($blocks[1])
        ), $output);

        $this->output->write($output);
    }

    private function drawIterations(Variant $variant)
    {
        $subject = $variant->getSubject();
        $this->output->write("\x1B[2K"); // clear the whole line
        $this->output->write(PHP_EOL);
        $this->output->write("\x1B[2K"); // clear the whole line
        $this->output->write("\x1B[1A");

        $sigma = 2;
        $bins = 16;

        if ($variant->isComputed()) {
            $times = $variant->getMetricValues(ComputedResult::class, 'z_value');
            $stats = $variant->getStats();
            $freqs = Statistics::histogram($times, $bins, -$sigma, $sigma);
        } else {
            $stats = new Distribution([0]);
            $freqs = array_fill(0, $bins + 1, null);
        }

        $this->output->write(sprintf(
            '#%-2d (σ = %s ) -%sσ [',
            $subject->getIndex(),
            $this->timeUnit->format($stats->getStdev()),
            $sigma
        ));
        $this->drawBlocks($freqs);

        $this->output->write(sprintf(
            '] +%sσ <comment>%s</comment>',
            $sigma,
            $variant->isComputed() ? $this->formatIterationsShortSummary($variant) : ''
        ));

        $this->output->write(PHP_EOL);
    }
}
