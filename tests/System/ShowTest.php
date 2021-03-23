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
        $document = $this->getResult(null, ' --store');
        $uuid = $document->evaluate('string(./suite/@uuid)');
        $process = $this->phpbench(
            'show ' . $uuid
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertStringContainsString(<<<'EOT'
benchNothing-\PhpBench\Tests\System\benchmarks\set4\NothingBench
+-----+------+-----+-----------+----------+----------+----------+--------+
| set | revs | its | mem_peak  | best     | mode     | worst    | rstdev |
+-----+------+-----+-----------+----------+----------+----------+--------+
| 0   | 1    | 1   | 100 bytes | 10.000μs | 10.000μs | 10.000μs | 0.2%   |
+-----+------+-----+-----------+----------+----------+----------+--------+
EOT
    , $output);
    }

    /**
     * It should show a specific report.
     */
    public function testSpecificReport(): void
    {
        $document = $this->getResult(null, ' --store');
        $uuid = $document->evaluate('string(./suite/@uuid)');
        $process = $this->phpbench(
            'show ' . $uuid . ' --report=default'
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertStringContainsString(<<<'EOT'
benchNothing-benchNothing
+------+-----+------+-----------+----------+--------------+----------------+
| iter | set | revs | mem_peak  | time_avg | comp_z_value | comp_deviation |
+------+-----+------+-----------+----------+--------------+----------------+
| 0    | 0   | 1    | 100 bytes | 10.000μs | 0            | 0              |
+------+-----+------+-----------+----------+--------------+----------------+
EOT
    , $output);
    }
}
