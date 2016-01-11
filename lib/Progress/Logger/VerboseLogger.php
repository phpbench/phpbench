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
use PhpBench\Util\Format;
use PhpBench\Util\TimeUnit;

class VerboseLogger extends PhpBenchLogger
{
    /**
     * @var int
     */
    private $rejectionCount = 0;

    /**
     * {@inheritdoc}
     */
    public function benchmarkStart(BenchmarkMetadata $benchmark)
    {
        $this->output->writeln(sprintf('<comment>%s</comment>', $benchmark->getClass()));
        $this->output->write(PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function benchmarkEnd(BenchmarkMetadata $benchmark)
    {
        $this->output->write(PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function iterationStart(Iteration $iteration)
    {
        $this->output->write(sprintf(
            "\x1B[0G    %-30s%sI%s P%s ",
            $iteration->getSubject()->getName(),
            $this->rejectionCount ? 'R' . $this->rejectionCount . ' ' : '',
            $iteration->getIndex(),
            $iteration->getParameters()->getIndex()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function iterationsStart(IterationCollection $iterations)
    {
        $this->paramSetIndex = $iterations->getParameterSet()->getIndex();
    }

    /**
     * {@inheritdoc}
     */
    public function iterationsEnd(IterationCollection $iterations)
    {
        if ($iterations->hasException()) {
            $this->output->write(sprintf(
                "\x1B[0G    %-30s<error>ERROR</error>",
                $iterations->getSubject()->getName()
            ));
            $this->output->write(PHP_EOL);

            return;
        }

        $stats = $iterations->getStats();
        $timeUnit = $this->timeUnit->resolveDestUnit($iterations->getSubject()->getOutputTimeUnit());
        $mode = $this->timeUnit->resolveMode($iterations->getSubject()->getOutputMode());

        $this->output->write(sprintf(
            "\t[μ Mo]/r: %s %s (%s) \t[μSD μRSD]/r: %s %s%%",

            $this->timeUnit->format($stats['mean'], $timeUnit, $mode, null, false),
            $this->timeUnit->format($stats['mode'], $timeUnit, $mode, null, false),
            $this->timeUnit->getDestSuffix($timeUnit, $mode),
            $this->timeUnit->format($stats['stdev'], $timeUnit, TimeUnit::MODE_TIME),
            number_format($stats['rstdev'], 2)
        ));
        $this->output->write(PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function retryStart($rejectionCount)
    {
        $this->rejectionCount = $rejectionCount;
        $this->output->write("\x1B[1F\x1B[0K");
    }
}
