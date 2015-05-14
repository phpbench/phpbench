<?php

namespace PhpBench\Tests\Functional\Console\Command;

use PhpBench\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class BaseCommandTestCase extends \PHPUnit_Framework_TestCase
{
    const TEST_FNAME = 'test.xml';

    public function tearDown()
    {
        if (file_exists(self::TEST_FNAME)) {
            unlink(self::TEST_FNAME);
        }
    }

    protected function runCommand($commandName, $arguments)
    {
        $application = new Application();
        $command = $application->find($commandName);
        $tester = new CommandTester($command);
        $tester->execute($arguments);

        return $tester;
    }
}
