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

class DeleteTest extends SystemTestCase
{
    /**
     * It should delete a specific UUID.
     */
    public function testLog()
    {
        $document = $this->getResult(null, ' --store');
        $uuid = $document->evaluate('string(./suite/@uuid)');

        $process = $this->phpbench(
            'delete --uuid=' . $uuid
        );
        $this->assertEquals(0, $process->getExitCode());

        // should throw an exception because the UUID does not exist.
        $process = $this->phpbench(
            'delete --uuid=' . $uuid
        );
        $this->assertEquals(1, $process->getExitCode());
    }
}
