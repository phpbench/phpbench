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

namespace PhpBench\Benchmarks\Macro;

/**
 * Benchmark for the log command.
 *
 * @BeforeClassMethods({"createWorkspace", "createResults"})
 */
class LogBench extends BaseBenchCase
{
    public static function createResults()
    {
        for ($i = 0; $i < 10; $i++) {
            // instantiate the benchmark class (this) so that we can
            // run a command.
            $case = new self();
            $case->runCommand('console.command.run', [
                'path' => self::getFunctionalBenchmarkPath(),
                '--executor' => 'debug',
                '--iterations' => [100],
                '--store' => true,
                '--progress' => 'none',
            ]);
        }
    }

    public function benchLog()
    {
        $this->runCommand('console.command.log', [
            '--no-pagination' => true,
        ]);
    }
}
