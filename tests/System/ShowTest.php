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

class ShowTest extends SystemTestCase
{
    /**
     * It should show a report for a specific run.
     */
    public function testDefaultReport(): void
    {
        $document = $this->getBenchResult(null, ' --store');
        $uuid = $document->evaluate('string(./suite/@uuid)');
        $process = $this->phpbench(
            'show ' . $uuid
        );

        $this->assertExitCode(0, $process);
    }

    /**
     * It should show a specific report.
     */
    public function testSpecificReport(): void
    {
        $document = $this->getBenchResult(null, ' --store');
        $uuid = $document->evaluate('string(./suite/@uuid)');
        $process = $this->phpbench(
            'show ' . $uuid . ' --report=default'
        );

        $this->assertExitCode(0, $process);
    }
}
