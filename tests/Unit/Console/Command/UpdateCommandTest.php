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

namespace PhpBench\Tests\Unit\Console\Command;

use Humbug\SelfUpdate\Updater;
use PhpBench\Console\Command\SelfUpdateCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class UpdateCommandTest extends TestCase
{
    private $updater;
    private $command;
    private $output;

    protected function setUp(): void
    {
        $this->updater = $this->prophesize(Updater::class);
        $this->command = new SelfUpdateCommand($this->updater->reveal());

        $this->output = new BufferedOutput();
    }

    /**
     * It should update the PHAR.
     */
    public function testUpdate()
    {
        $input = new ArrayInput([], $this->command->getDefinition());
        $this->updater->update()->shouldBeCalled()->willReturn(true);
        $this->updater->getOldVersion()->willReturn('10');
        $this->updater->getNewVersion()->willReturn('20');
        $this->command->execute($input, $this->output);
        $this->assertStringContainsString(
            'PHPBench was updated from "10" to "20"',
            $this->output->fetch()
        );
    }

    /**
     * It should show a message if no update is required.
     */
    public function testUpdateNotRequired()
    {
        $input = new ArrayInput([], $this->command->getDefinition());
        $this->updater->update()->shouldBeCalled()->willReturn(false);
        $this->command->execute($input, $this->output);
        $this->assertStringContainsString(
            'No update required',
            $this->output->fetch()
        );
    }

    /**
     * It should rollback.
     */
    public function testUpdateRollback()
    {
        $input = new ArrayInput([
            '--rollback' => true,
        ], $this->command->getDefinition());
        $this->updater->rollback()->shouldBeCalled()->willReturn(true);
        $this->command->execute($input, $this->output);

        $this->assertStringContainsString(
            'Successfully rolled back',
            $this->output->fetch()
        );
    }

    /**
     * It should show an error if it could not roll back.
     *
     */
    public function testUpdateRollbackError()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not rollback');
        $input = new ArrayInput([
            '--rollback' => true,
        ], $this->command->getDefinition());
        $this->updater->rollback()->shouldBeCalled()->willReturn(false);
        $this->command->execute($input, $this->output);
    }
}
