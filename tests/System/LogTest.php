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

class LogTest extends SystemTestCase
{
    /**
     * It should show the log.
     */
    public function testLog()
    {
        $this->getResult(null, ' --store');
        $process = $this->phpbench(
            'log'
        );
        $output = $process->getOutput();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertCount(9, explode("\n", $output));
    }
}
