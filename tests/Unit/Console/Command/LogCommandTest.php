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

use PhpBench\Console\CharacterReader;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Console\Command\LogCommand;
use PhpBench\Registry\Registry;
use PhpBench\Storage\DriverInterface;
use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\HistoryIteratorInterface;
use PhpBench\Util\TimeUnit;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class LogCommandTest extends TestCase
{
    private $storage;
    private $command;
    private $driver;
    private $output;
    private $history;

    protected function setUp(): void
    {
        if (!class_exists(QuestionHelper::class)) {
            $this->markTestSkipped('Not testing if QuestionHelper class does not exist (< Symfony 2.7)');
        }

        $this->storage = $this->prophesize(Registry::class);
        $this->timeUnit = $this->prophesize(TimeUnit::class);
        $this->timeUnitHandler = $this->prophesize(TimeUnitHandler::class);
        $this->characterReader = $this->prophesize(CharacterReader::class);

        $this->command = new LogCommand(
            $this->storage->reveal(),
            $this->timeUnit->reveal(),
            $this->timeUnitHandler->reveal(),
            $this->characterReader->reveal()
        );

        $this->application = new Application();
        $this->command->setApplication($this->application);

        $this->driver = $this->prophesize(DriverInterface::class);
        $this->storage->getService()->willReturn($this->driver->reveal());
        $this->output = new BufferedOutput();

        $this->history = $this->prophesize(HistoryIteratorInterface::class);

        $this->timeUnit->toDestUnit(Argument::cetera())->will(function ($time) {
            return $time[0];
        });
        $this->timeUnit->format(Argument::cetera())->will(function ($time) {
            return $time[0];
        });
        $this->timeUnit->getDestSuffix()->willReturn('s');
    }

    protected function tearDown(): void
    {
        $this->resetTerminalDimensions();
    }

    /**
     * It should be configured.
     */
    public function testConfigure()
    {
        $this->command->configure();
        $this->addToAssertionCount(1);
    }

    /**
     * It should show the history.
     */
    public function testHistory()
    {
        $input = new ArrayInput([
            '--no-pagination' => true,
        ], $this->command->getDefinition());

        $this->setTerminalDimensions(100, 10);

        $this->characterReader->read()->shouldNotBeCalled();

        $this->driver->history()->willReturn($this->history->reveal());
        $this->history->rewind()->shouldBeCalled();
        $this->history->valid()->willReturn(true, true, true, false);
        $this->history->key()->willReturn(0, 1, 2);
        $this->history->next()->shouldBeCalled();
        $this->history->current()->willReturn(
            $this->createHistoryEntry(1),
            $this->createHistoryEntry(2),
            $this->createHistoryEntry(3)
        );

        $this->command->execute($input, $this->output);

        $output = $this->output->fetch();

        $expected = <<<'EOT'
run 1
Date:    2016-01-01T00:00:00+02:00
Branch:  branch1
Tag:     foo
Scale:   10 subjects, 20 iterations, 40 revolutions
Summary: (best [mean] worst) = 0.500 [1.250] 2.000 (s)
         ⅀T: 100 μRSD/r: 0.750%

run 2
Date:    2016-01-01T00:00:00+02:00
Branch:  branch2
Tag:     foo
Scale:   10 subjects, 20 iterations, 40 revolutions
Summary: (best [mean] worst) = 0.500 [1.250] 2.000 (s)
         ⅀T: 100 μRSD/r: 0.750%

run 3
Date:    2016-01-01T00:00:00+01:00
Branch:  branch3
Tag:     foo
Scale:   10 subjects, 20 iterations, 40 revolutions
Summary: (best [mean] worst) = 0.500 [1.250] 2.000 (s)
         ⅀T: 100 μRSD/r: 0.750%


EOT;

        $this->assertEquals($this->replaceDate($expected), $this->replaceDate($output));
    }

    /**
     * It should paginate.
     */
    public function testPaginate()
    {
        $input = new ArrayInput([], $this->command->getDefinition());
        $output = $this->output;

        $this->setTerminalDimensions(100, 14);

        $this->characterReader->read()->willReturn('')->shouldBeCalledTimes(1);

        $this->driver->history()->willReturn($this->history->reveal());
        $this->history->rewind()->shouldBeCalled();
        $this->history->valid()->willReturn(true, true, true, false);
        $this->history->key()->willReturn(0, 1, 2);
        $this->history->next()->shouldBeCalled();
        $this->history->current()->willReturn(
            $this->createHistoryEntry(1),
            $this->createHistoryEntry(2),
            $this->createHistoryEntry(3)
        );

        $this->command->execute($input, $this->output);

        $output = $this->output->fetch();

        $expected = <<<'EOT'
run 1
Date:    2016-01-01T00:00:00+01:00
Branch:  branch1
Tag:     foo
Scale:   10 subjects, 20 iterations, 40 revolutions
Summary: (best [mean] worst) = 0.500 [1.250] 2.000 (s)
         ⅀T: 100 μRSD/r: 0.750%

run 2
Date:    2016-01-01T00:00:00+01:00
Branch:  branch2
Tag:     foo
Scale:   10 subjects, 20 iterations, 40 revolutions
lines 0-13 any key to continue, <q> to quit
run 3
Date:    2016-01-01T00:00:00+01:00
Branch:  branch3
Tag:     foo
Scale:   10 subjects, 20 iterations, 40 revolutions
Summary: (best [mean] worst) = 0.500 [1.250] 2.000 (s)
         ⅀T: 100 μRSD/r: 0.750%


EOT;

        $this->assertEquals($this->replaceDate($expected), $this->replaceDate($output));
    }

    /**
     * It should quit pagination.
     */
    public function testQuitPagination()
    {
        $input = new ArrayInput([], $this->command->getDefinition());
        $output = $this->output;

        $this->setTerminalDimensions(100, 14);

        $this->characterReader->read()->willReturn('q')->shouldBeCalledTimes(1);

        $this->driver->history()->willReturn($this->history->reveal());
        $this->history->rewind()->shouldBeCalled();
        $this->history->valid()->willReturn(true, true);
        $this->history->key()->willReturn(0, 1);
        $this->history->next()->shouldBeCalled();
        $this->history->current()->willReturn(
            $this->createHistoryEntry(1),
            $this->createHistoryEntry(2),
            $this->createHistoryEntry(3)
        );

        $this->command->execute($input, $this->output);

        $output = $this->output->fetch();

        $expected = <<<'EOT'
run 1
Date:    2016-01-01T00:00:00+01:00
Branch:  branch1
Tag:     foo
Scale:   10 subjects, 20 iterations, 40 revolutions
Summary: (best [mean] worst) = 0.500 [1.250] 2.000 (s)
         ⅀T: 100 μRSD/r: 0.750%

run 2
Date:    2016-01-01T00:00:00+01:00
Branch:  branch2
Tag:     foo
Scale:   10 subjects, 20 iterations, 40 revolutions
lines 0-13 any key to continue, <q> to quit
EOT;

        $this->assertEquals($this->replaceDate($expected), $this->replaceDate($output));
    }

    private function replaceDate($string)
    {
        return preg_replace('{\+[0-9]{2}:[0-9]{2}}', '00:00', $string);
    }

    private function createHistoryEntry($index)
    {
        return new HistoryEntry(
            $index,
            new \DateTime('2016-01-01'),
            'foo', 'branch' . $index,
            10,
            20,
            40,
            0.5,
            2,
            1.25,
            0.75,
            100
        );
    }

    private function setTerminalDimensions(int $columns, int $lines): void
    {
        putenv('COLUMNS=' . $columns);
        putenv('LINES=' . $lines);
    }

    private function resetTerminalDimensions(): void
    {
        putenv('COLUMNS');
        putenv('LINES');
    }
}
