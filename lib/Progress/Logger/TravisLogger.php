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
use PhpBench\Model\Suite;
use PhpBench\Model\Variant;

class TravisLogger extends PhpBenchLogger
{
    /**
     * {@inheritdoc}
     */
    public function benchmarkStart(Benchmark $benchmark)
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
    public function variantEnd(Variant $variant)
    {
        if ($variant->getRejectCount() > 0) {
            return;
        }

        $subject = $variant->getSubject();

        if ($variant->hasErrorStack()) {
            $this->output->writeln(sprintf(
                '    t%-30s <error>ERROR</error>',
                $subject->getName()
            ));

            return;
        }

        $this->output->writeln(sprintf(
            "    %-30s I%s P%s\t%s",
            $subject->getName(),
            $variant->count(),
            $variant->getParameterSet()->getIndex(),
            $this->formatIterationsFullSummary($variant)
        ));
    }

    public function endSuite(Suite $suite)
    {
        $this->output->write(PHP_EOL);
        parent::endSuite($suite);
    }
}
