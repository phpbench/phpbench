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

class EvalTest extends SystemTestCase
{
    public function testEvalExpression(): void
    {
        $process = $this->phpbench(
            'eval 10+2'
        );
        $output = $process->getOutput();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertStringContainsString('12', $process->getOutput());
    }
}

