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
    public function testDefaultReport()
    {
        $document = $this->getResult(null, ' --store');
        $uuid = $document->evaluate('string(./suite/@uuid)');
        $process = $this->phpbench(
            'show ' . $uuid
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertStringContainsString(<<<'EOT'
+--------------+--------------+-----+------+-----+----------+----------+----------+----------+----------+---------+--------+-------+
| benchmark    | subject      | set | revs | its | mem_peak | best     | mean     | mode     | worst    | stdev   | rstdev | diff  |
+--------------+--------------+-----+------+-----+----------+----------+----------+----------+----------+---------+--------+-------+
| NothingBench | benchNothing | 0   | 1    | 1   | 100b     | 10.000μs | 10.000μs | 10.000μs | 10.000μs | 0.000μs | 0.00%  | 1.00x |
+--------------+--------------+-----+------+-----+----------+----------+----------+----------+----------+---------+--------+-------+
EOT
    , $output);
    }

    /**
     * It should show a specific report.
     */
    public function testSpecificReport()
    {
        $document = $this->getResult(null, ' --store');
        $uuid = $document->evaluate('string(./suite/@uuid)');
        $process = $this->phpbench(
            'show ' . $uuid . ' --report=default'
        );

        $this->assertExitCode(0, $process);
        $output = $process->getOutput();
        $this->assertStringContainsString(<<<'EOT'
+--------------+--------------+-----+------+------+----------+----------+--------------+----------------+
| benchmark    | subject      | set | revs | iter | mem_peak | time_rev | comp_z_value | comp_deviation |
+--------------+--------------+-----+------+------+----------+----------+--------------+----------------+
| NothingBench | benchNothing | 0   | 1    | 0    | 100b     | 10.000μs | +0.00σ       | +0.00%         |
+--------------+--------------+-----+------+------+----------+----------+--------------+----------------+
EOT
    , $output);
    }
}
