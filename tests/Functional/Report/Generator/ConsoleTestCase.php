<?php

namespace PhpBench\Tests\Functional\Report\Generator;

use PhpBench\Container;
use Symfony\Component\Console\Output\BufferedOutput;
use PhpBench\Benchmark\SuiteDocument;

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

