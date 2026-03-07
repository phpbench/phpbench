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

namespace PhpBench\Tests\Unit\Progress\Logger;

use PhpBench\Model\Result\TimeResult;
use PhpBench\Progress\Logger\JsonLogger;
use PhpBench\Tests\Util\VariantBuilder;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class JsonLoggerTest extends LoggerTestCase
{
    public function testIterationsEnd(): void
    {
        $variant = VariantBuilder::create('hello variant')
            ->iteration()->setResult(new TimeResult(100, 1))->end()
            ->build();
        $output = new BufferedOutput();

        $logger = $this->createLogger($output);

        $logger->iterationEnd($variant->getIteration(0));

        self::assertEquals([
            'benchmark' => 'testBenchmark',
            'subject' => 'foo',
            'variant' => 'hello variant',
            'iteration' => 0,
            'time_net' => 100,
            'time_revs' => 1,
            'comp_z_value' => 0,
            'comp_deviation' => 0,
            'time_avg' => 100,
        ], json_decode($output->fetch(), true));
    }


    private function createLogger(OutputInterface $output): JsonLogger
    {
        return new JsonLogger($output);
    }
}
