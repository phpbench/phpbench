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
use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Output\OutputInterface;

class DotsLogger extends PhpBenchLogger
{
    private $showBench;
    private $buffer;
    private $isCi = false;
    private $firstTime = true;

    public function __construct(OutputInterface $output, TimeUnit $timeUnit, $showBench = false)
    {
        parent::__construct($output, $timeUnit);
        $this->showBench = $showBench;

        // if we are in travis, don't do any fancy stuff.
        $this->isCi = getenv('CONTINUOUS_INTEGRATION') ? true : false;
    }

    public function benchmarkStart(Benchmark $benchmark): void
    {
        if ($this->showBench) {
            // do not output a line break on the first run
            if (false === $this->firstTime) {
                $this->output->writeln('');
            }
            $this->firstTime = false;

            $this->output->writeln($benchmark->getClass());
        }
    }

    public function variantEnd(Variant $variant): void
    {
        // do not show reject runs
        if ($variant->getRejectCount() > 0) {
            return;
        }

        $dot = '.';

        if ($variant->getAssertionResults()->hasFailures()) {
            $dot = '<error>F</error>';
        }

        if ($variant->hasErrorStack()) {
            $dot = '<error>E</error>';
        }

        if ($this->isCi) {
            $this->output->write($dot);

            return;
        }

        $this->buffer .= $dot;
        $this->output->write(sprintf(
            "\x0D%s ",
            $this->buffer
        ));
    }

    public function iterationStart(Iteration $iteration): void
    {
        if ($this->isCi) {
            return;
        }

        $state = $iteration->getIndex() % 4;
        $states = [
            0 => '|',
            1 => '/',
            2 => '-',
            3 => '\\',
        ];

        $this->output->write(sprintf(
            "\x0D%s%s",
            $this->buffer,
            $states[$state]
        ));
    }

    public function endSuite(Suite $suite): void
    {
        $this->output->write(PHP_EOL . PHP_EOL);
        parent::endSuite($suite);
    }
}
