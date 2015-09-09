<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Report\Generator;

use PhpBench\Benchmark\SuiteDocument;
use Symfony\Component\Console\Output\BufferedOutput;

abstract class ConsoleTestCase extends GeneratorTestCase
{
    private $output;

    protected function getOutput()
    {
        if ($this->output) {
            return $this->output;
        }

        $this->output = new BufferedOutput();

        return $this->output;
    }

    protected function assertStringCount($count, $string, $subject)
    {
        $this->assertEquals($count, substr_count($subject, $string));
    }

    protected function generate(SuiteDocument $document, $config)
    {
        $this->getGenerator()->generate(
            $document,
            $this->getConfig($config)
        );
    }
}
