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

namespace PhpBench\Examples\Benchmark\Macro;

use PhpBench\Console\Command\LogCommand;
use PhpBench\Console\Command\RunCommand;

/**
 * Benchmark for the log command.
 *
 * @BeforeClassMethods({"resetWorkspace", "createResults"})
 */
class LogBench extends BaseBenchCase
{
    public static function createResults()
    {
        for ($i = 0; $i < 10; $i++) {
            // instantiate the benchmark class (this) so that we can
            // run a command.
            $case = new self();
            $case->runCommand(RunCommand::class, [
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
        $this->runCommand(LogCommand::class, [
            '--no-pagination' => true,
            '--limit' => 1
        ]);
    }
}
