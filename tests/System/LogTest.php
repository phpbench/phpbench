<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\System;

class LogTest extends SystemTestCase
{
    /**
     * It should show an error when no storage engine is configured.
     */
    public function testExceptionWhenNoStorageConfigured()
    {
        $this->createResult();
        $process = $this->phpbench(
            'log --no-pagination'
        );
        $this->assertEquals(1, $process->getExitCode());
        $output = $process->getErrorOutput();
        $this->assertContains('You must configure a default storage service, registered storage services', $output);
    }

    /**
     * It should show the history.
     */
    public function testLog()
    {
        $this->markTestSkipped('Cannot functionaly test the log command until a default storage implementation is available.');
    }
}
