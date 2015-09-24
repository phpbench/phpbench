<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Bin;

class BinTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should use a speified, valid, configuration.
     */
    public function testSpecifiedConfig()
    {
        list($exitCode, $results) = $this->execCommand('.', 'run --verbose --config=env/config_valid/phpbench.json');
        $this->assertExitCodeZero($exitCode, $results);
        $this->assertContains('Done', $results);
    }

    /**
     * It should use phpbench.json if present
     * It should prioritize phpbench.json over .phpbench.dist.json.
     */
    public function testPhpBenchConfig()
    {
        list($exitCode, $results) = $this->execCommand('env/config_valid', 'run');
        $this->assertExitCodeZero($exitCode, $results);
        $this->assertContains('Done', $results);
    }

    /**
     * It should use phpbench.json.dist if present.
     */
    public function testPhpBenchDistConfig()
    {
        list($exitCode, $results) = $this->execCommand('env/config_dist', 'run');
        $this->assertExitCodeZero($exitCode, $results);
        $this->assertContains('Done', $results);
    }

    private function execCommand($env, $command)
    {
        chdir(__DIR__ . '/' . $env);
        $command = 'php ' . __DIR__ . '/../../../bin/phpbench ' . $command;
        exec($command, $result, $status);

        return array($status, implode($result, PHP_EOL));
    }

    private function assertExitCodeZero($exitCode, $output)
    {
        if ($exitCode !== 0) {
            $this->fail(sprintf(
                'Exit code was "%s": %s',
                $exitCode, $output
            ));
        }
    }
}
