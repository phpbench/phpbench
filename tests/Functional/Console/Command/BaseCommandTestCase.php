<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Console\Command;

use PhpBench\Console\Application;
use PhpBench\DependencyInjection\Container;
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

    protected function runCommand($commandName, array $arguments)
    {
        $container = new Container();
        $container->configure();
        $container->build();
        $application = $container->get('console.application');
        $command = $application->find($commandName);
        $tester = new CommandTester($command);
        $tester->execute($arguments);

        return $tester;
    }
}
