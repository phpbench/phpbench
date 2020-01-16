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

namespace PhpBench\Extensions\XDebug\Tests\System;

class ProfileCommandTest extends XDebugTestCase
{
    /**
     * It should run when given a path.
     * It should show the default (simple) report.
     */
    public function testCommand()
    {
        $process = $this->phpbench('xdebug:profile benchmarks/set1/BenchmarkBench.php --filter=benchDoNothing');
        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('profile(s) generated', $process->getOutput());
        $lines = explode("\n", $process->getOutput());

        // get the filename from the output and check it exists.
        $line = trim($lines[count($lines) - 2]);
        $this->assertFileExists($line);
    }

    /**
     * It die if an unknown gui-bin is specified.
     */
    public function testCommandBadGui()
    {
        $process = $this->phpbench('xdebug:profile benchmarks/set1/BenchmarkBench.php --gui --gui-bin=idontexistdotcom1234');
        $this->assertStringContainsString(
            'Could not locate GUI bin "idontexistdotcom1234"',
            $process->getErrorOutput()
        );
        $this->assertExitCode(1, $process);
    }

    /**
     * It should launch a gui.
     */
    public function testGui()
    {
        $env = getenv('PATH');
        putenv('PATH=' . $env . ':' . __DIR__ . '/bin');
        $process = $this->phpbench('xdebug:profile benchmarks/set1/BenchmarkBench.php --gui --gui-bin=foogrind');
        putenv('PATH=' . $env);
        $this->assertExitCode(0, $process);
    }

    /**
     * Specify custom output dir.
     */
    public function testOutputDir()
    {
        $process = $this->phpbench('xdebug:profile benchmarks/set1/BenchmarkBench.php --outdir=profilenew --filter=benchDoNothing');
        $lines = explode("\n", $process->getOutput());
        // get the filename from the output and check it exists.
        $line = trim($lines[count($lines) - 2]);
        $this->assertStringContainsString('profilenew', $line);
        $this->assertFileExists($line);
    }
}
