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

use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;

class BlinkenLogger extends AnsiLogger
{
    /**
     * Number of measurements to show per row.
     */
    const NUMBER_COLS = 15;

    const INDENT = 4;

    /**
     * Track rejected iterations.
     *
     * @var bool[]
     */
    private $rejects = [];

    /**
     * Current number of rows in the time display.
     *
     * @var int
     */
    private $currentLine = 0;

    /**
     * Column width.
     *
     * @var int
     */
    private $colWidth = 6;
    private $firstTime = true;

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
        if (false === $this->firstTime) {
            $this->output->write(PHP_EOL);
        }
        $this->firstTime = true;
        $this->output->write(sprintf('<comment>%s</comment>', $benchmark->getClass()));

        $subjectNames = [];

        foreach ($benchmark->getSubjects() as $index => $subject) {
            $subjectNames[] = sprintf('#%s %s', $index, $subject->getName());
        }

        $this->output->write(sprintf(' (%s)', implode(', ', $subjectNames)));
        $this->output->write(PHP_EOL);
        $this->output->write(PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function variantStart(Variant $variant)
    {
        $this->drawIterations($variant, $this->rejects, 'error');
        $this->renderCollectionStatus($variant);
        $this->resetLinePosition(); // put cursor at starting ypos ready for iteration times
    }

    /**
     * {@inheritdoc}
     */
    public function variantEnd(Variant $variant)
    {
        $this->resetLinePosition();
        $this->drawIterations($variant, [], null);

        if ($variant->hasErrorStack()) {
            $this->output->write(' <error>ERROR</error>');
            $this->output->write("\x1B[0J"); // clear the rest of the line
            $this->output->write(PHP_EOL);

            return;
        }

        if ($variant->hasFailed()) {
            $this->output->write(' <error>FAIL</error>');
        }

        $this->rejects = [];

        foreach ($variant->getRejects() as $reject) {
            $this->rejects[$reject->getIndex()] = true;
        }

        if ($this->rejects) {
            $this->resetLinePosition();

            return;
        }

        $this->output->write(sprintf(
            ' <comment>%s</comment>',
            $this->formatIterationsShortSummary($variant)
        ));
        $this->output->write(PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function iterationEnd(Iteration $iteration)
    {
        $time = $this->formatIterationTime($iteration);
        $this->output->write(sprintf(
            "\x1B[" . $this->getXPos($iteration) . 'G%s',
            $time
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function iterationStart(Iteration $iteration)
    {
        if ($this->currentLine != $yPos = $this->getYPos($iteration)) {
            $downMovement = $yPos - $this->currentLine;
            $this->output->write("\x1B[" . $downMovement . 'B');
            $this->currentLine = $yPos;
        }

        $time = $this->formatIterationTime($iteration);

        $this->output->write(sprintf(
            "\x1B[" . $this->getXPos($iteration) . 'G<bg=green>%s</>',
            $time
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function formatIterationTime(Iteration $iteration)
    {
        $time = sprintf('%-' . $this->colWidth . 's', parent::formatIterationTime($iteration));

        if (strlen(trim($time)) >= $this->colWidth) {
            $this->colWidth += 2;
            $this->resetLinePosition();
            $this->drawIterations($iteration->getVariant(), $this->rejects, 'error');
            $this->resetLinePosition();
        }

        return $time;
    }

    private function drawIterations(Variant $variant, array $specials, $tag)
    {
        $this->output->write("\x1B[2K"); // clear line

        $timeUnit = $variant->getSubject()->getOutputTimeUnit();
        $outputMode = $variant->getSubject()->getOutputMode();
        $lines = [];
        $line = sprintf('%-' . self::INDENT . 's', '#' . $variant->getSubject()->getIndex());
        $nbIterations = $variant->count();

        for ($index = 0; $index < $nbIterations; $index++) {
            $iteration = $variant->getIteration($index);

            $displayTime = $this->formatIterationTime($iteration);

            if (isset($specials[$iteration->getIndex()])) {
                $displayTime = sprintf('<%s>%' . $this->colWidth . 's</%s>', $tag, $displayTime, $tag);
            }

            $line .= $displayTime;

            if ($index > 0 && $index < $nbIterations - 1 && ($index + 1) % self::NUMBER_COLS == 0) {
                $lines[] = $line;
                $line = str_repeat(' ', self::INDENT);
            }
        }

        $lines[] = $line;
        $this->currentLine = count($lines) - 1;

        $output = trim(implode(PHP_EOL, $lines));
        $output .= sprintf(
            ' (%s)',
            $this->timeUnit->getDestSuffix(
                $this->timeUnit->resolveDestUnit($timeUnit),
                $this->timeUnit->resolveMode($outputMode)
            )
        );

        $this->output->write(sprintf("%s\x1B[0J", $output)); // clear rest of the line
    }

    private function getXPos(Iteration $iteration)
    {
        return self::INDENT + ($iteration->getIndex() % self::NUMBER_COLS) * $this->colWidth + 1;
    }

    private function getYPos(Iteration $iteration)
    {
        return floor($iteration->getIndex() / self::NUMBER_COLS);
    }

    private function resetLinePosition()
    {
        if ($this->currentLine) {
            $this->output->write("\x1B[" . $this->currentLine . 'A'); // reset cursor Y pos
        }
        $this->currentLine = 0;

        $xPos = 0;
        $this->output->write("\x1B[" . $xPos . 'G');
    }
}
