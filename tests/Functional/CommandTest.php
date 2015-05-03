<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

use PhpBench\Console\Command\BenchRunCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends \PHPUnit_Framework_TestCase
{
    public function testCommand()
    {
        $tester = $this->runCommand(array(
            'path' => __DIR__ . '/../assets/functional',
        ));
        $this->assertEquals(0, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertContains('Running benchmarking suite', $display);
        $this->assertContains('Parameterized bench mark', $display);
    }

    private function runCommand($arguments)
    {
        $application = new Application();
        $application->add(new BenchRunCommand());
        $command = $application->find('phpbench:run');
        $tester = new CommandTester($command);
        $tester->execute($arguments);

        return $tester;
    }
}
