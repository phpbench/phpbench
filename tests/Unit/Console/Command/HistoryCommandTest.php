<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Console\Command;

use PhpBench\Console\Command\HistoryCommand;
use PhpBench\Storage\HistoryEntry;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class HistoryCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->storage = $this->prophesize('PhpBench\Storage\DriverFactory');
        $this->command = new HistoryCommand(
            $this->storage->reveal()
        );
        $this->driver = $this->prophesize('PhpBench\Storage\DriverInterface');
        $this->storage->getDriver()->willReturn($this->driver->reveal());
        $this->output = new BufferedOutput();

        $this->history = $this->prophesize('PhpBench\Storage\HistoryIteratorInterface');
    }

    /**
     * It should be configured.
     */
    public function testConfigure()
    {
        $this->command->configure();
    }

    /**
     * It should show the history.
     */
    public function testHistory()
    {
        $input = new ArrayInput(array(
            '--limit' => 10,
        ), $this->command->getDefinition());

        $this->driver->history()->willReturn($this->history->reveal());
        $this->history->rewind()->shouldBeCalled();
        $this->history->valid()->willReturn(true, true, false);
        $this->history->key()->willReturn(0, 1, 2);
        $this->history->next()->shouldBeCalled();
        $this->history->current()->willReturn(
            new HistoryEntry(1, new \DateTime('2016-01-01'), 'foo', 'branch1'),
            new HistoryEntry(2, new \DateTime('2016-01-01'), 'foo', 'branch2'),
            new HistoryEntry(3, new \DateTime('2016-01-01'), 'foo', 'branch3')
        );

        $this->command->execute($input, $this->output);

        $output = $this->output->fetch();

        $expected = <<<'EOT'
Limit set to 10
Run     Date                    VCS Branch      Context

1       2016-01-01 00:00:00     branch1 foo
2       2016-01-01 00:00:00     branch2 foo
EOT;

        $this->assertEquals($this->squash($expected), $this->squash($output));
    }

    private function squash($string)
    {
        return str_replace(array(' ', "\n", "\t"), '', $string);
    }
}
