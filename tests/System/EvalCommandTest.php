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

namespace PhpBench\Tests\System;

use function escapeshellarg;

class EvalCommandTest extends SystemTestCase
{
    public function testEvaluate(): void
    {
        $this->getBenchResult(null, ' --store');
        $process = $this->phpbench(
            'eval "2 > 1"'
        );
        $output = $process->getOutput();
        self::assertEquals(0, $process->getExitCode());
    }

    public function testEvaluateWithParams(): void
    {
        $this->getBenchResult(null, ' --store');
        $process = $this->phpbench(
            'eval "2 > foobar" ' . escapeshellarg(json_encode(['foobar' => 3]))
        );
        self::assertEquals(0, $process->getExitCode());
    }
}
