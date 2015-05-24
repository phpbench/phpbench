<?php

/*
 * This file is part of the PHP Bench package
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
     * It should use a speified, valid,  configuration.
     */
    public function testSpecifiedConfig()
    {
        list($exitCode, $results) = $this->execCommand('.', 'run --config=env/config_valid/.phpbench');
        if ($exitCode != 0) {
            var_dump($results);
        }
        $this->assertEquals(0, $exitCode);
        $this->assertContains('Done', $results);
    }

    /**
     * It should use .phpbench if present
     * It should prioritize .phpbench over .phpbench.dist.
     */
    public function testPhpBenchConfig()
    {
        list($exitCode, $results) = $this->execCommand('env/config_valid', 'run');
        $this->assertEquals(0, $exitCode);
        $this->assertContains('Done', $results);
    }

    /**
     * It should use .phpbench.dist if present.
     */
    public function testPhpBenchDistConfig()
    {
        list($exitCode, $results) = $this->execCommand('env/config_dist', 'run');
        $this->assertEquals(0, $exitCode);
        $this->assertContains('Done', $results);
    }

    /**
     * It should exit with an error status if no configuration is present and no autoload is available.
     */
    public function testNoAutoload()
    {
        list($exitCode, $results) = $this->execCommand('.', 'run');
        $this->assertEquals(1, $exitCode);
        $this->assertContains('does not exist', $results);
    }

    /**
     * It should exit with an error status if the config file did not return a configuration object.
     */
    public function testConfigNoReturnConfiguration()
    {
        list($exitCode, $results) = $this->execCommand('env/config_no_configuration', 'run');
        $this->assertEquals(1, $exitCode);
        $this->assertContains('did not return', $results);
    }

    private function execCommand($env, $command)
    {
        chdir(__DIR__ . '/' . $env);
        $command = __DIR__ . '/../../../bin/phpbench ' . $command;
        exec($command, $result, $status);

        return array($status, implode($result, PHP_EOL));
    }
}
