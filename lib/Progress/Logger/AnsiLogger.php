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

abstract class AnsiLogger extends PhpBenchLogger
{
    protected function renderCollectionStatus(IterationCollection $collection)
    {
        $this->output->write(PHP_EOL);
        $this->output->write("\x1B[0J"); // clear the line
        $this->output->write(PHP_EOL);
        $this->output->write(sprintf(
            '<info>subject</info> %s<info> with </info>%s<info> iteration(s) of </info>%s<info> rev(s),</info>',
            sprintf('%s', $collection->getSubject()->getName()),
            count($collection),
            $collection->getRevolutions()
        ));
        $this->output->write(PHP_EOL);
        $this->output->write(sprintf(
            '<info>parameters</info> %s',
            json_encode($collection->getParameterSet()->getArrayCopy(), true)
        ));
        $this->output->write(PHP_EOL);
        $this->output->write("\x1B[". 4 . 'A'); // put the cursor back to the line with the measurements
        $this->output->write("\x1B[0G"); // put the cursor back at col 0
    }
}
