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

use PhpBench\Benchmark\IterationCollection;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\SuiteDocument;

class TravisLogger extends PhpBenchLogger
{
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
        $this->output->writeln(sprintf('<comment>%s</comment>', $benchmark->getClass()));
        $this->output->write(PHP_EOL);
    }

    /**
     * {@inheritdoc}
     */
    public function iterationsEnd(IterationCollection $iterations)
    {
        if ($iterations->getRejectCount() > 0) {
            return;
        }

        $subject = $iterations->getSubject();

        if ($iterations->hasException()) {
            $this->output->writeln(sprintf(
                '    t%-30s <error>ERROR</error>',
                $subject->getName()
            ));

            return;
        }

        $this->output->writeln(sprintf(
            "    %-30s I%s P%s\t%s",
            $subject->getName(),
            $iterations->count(),
            $iterations->getParameterSet()->getIndex(),
            $this->formatIterationsFullSummary($iterations)
        ));
    }

    public function endSuite(SuiteDocument $suiteDocument)
    {
        $this->output->write(PHP_EOL);
        parent::endSuite($suiteDocument);
    }
}
