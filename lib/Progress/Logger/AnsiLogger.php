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

use PhpBench\Model\Variant;

abstract class AnsiLogger extends PhpBenchLogger
{
    protected function renderCollectionStatus(Variant $variant)
    {
        $this->output->write(PHP_EOL);
        $this->output->write("\x1B[0J"); // clear the line
        $this->output->write(PHP_EOL);
        $this->output->write(sprintf(
            '<info>subject</info> %s<info> with </info>%s<info> iteration(s) of </info>%s<info> rev(s),</info>',
            sprintf('%s', $variant->getSubject()->getName()),
            count($variant),
            $variant->getRevolutions()
        ));
        $this->output->write(PHP_EOL);
        $this->output->write(sprintf(
            '<info>parameter set</info> %s',
            $variant->getParameterSet()->getName()
        ));
        $this->output->write(PHP_EOL);
        $this->output->write("\x1B[". 4 . 'A'); // put the cursor back to the line with the measurements
        $this->output->write("\x1B[0G"); // put the cursor back at col 0
    }
}
