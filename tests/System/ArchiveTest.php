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

class ArchiveTest extends SystemTestCase
{
    /**
     * It should show a report for a specific run.
     */
    public function testArchive()
    {
        $this->getResult(null, ' --store');
        $this->getResult(null, ' --store');
        $process = $this->phpbench(
            'archive'
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();

        // it should show two dots, one for each result
        $this->assertContains(<<<'EOT'
..
EOT
        , $output);

        $process = $this->phpbench(
            'archive --restore'
        );

        $output = $process->getOutput();
        $this->assertContains(<<<'EOT'
Restoring 0 of 2 suites
EOT
        , $output);
    }
}
