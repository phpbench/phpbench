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

use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Console\Command\LogCommand;
use PhpBench\Registry\Registry;
use PhpBench\Storage\DriverInterface;
use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\HistoryIteratorInterface;
use PhpBench\Util\TimeUnit;
use Prophecy\Argument;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class LogCommandTest extends \PHPUnit_Framework_TestCase
{
    private $storage;
    private $command;
    private $driver;
    private $output;
    private $history;

    public function setUp()
    {
        if (!class_exists(QuestionHelper::class)) {
            $this->markTestSkipped('Not testing if QuestionHelper class does not exist (< Symfony 2.7)');
        }

        $this->storage = $this->prophesize(Registry::class);
        $this->timeUnit = $this->prophesize(TimeUnit::class);
        $this->timeUnitHandler = $this->prophesize(TimeUnitHandler::class);
        $this->questionHelper = $this->prophesize(QuestionHelper::class);

        $this->command = new LogCommand(
            $this->storage->reveal(),
            $this->timeUnit->reveal(),
            $this->timeUnitHandler->reveal(),
            $this->questionHelper->reveal()
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
        $input = new ArrayInput([
            '--no-pagination' => true,
        ], $this->command->getDefinition());

        $this->application->setTerminalDimensions(100, 10);
        $this->questionHelper->ask(Argument::cetera())->shouldNotBeCalled();

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
Context: foo
Scale:   10 subjects, 20 iterations, 40 revolutions
Summary: (best [mean] worst) = 0.500 [1.250] 2.000 (s)
         ⅀T: 100 μRSD/r: 0.750%

run 2
Date:    2016-01-01T00:00:00+02:00
Branch:  branch2
Context: foo
Scale:   10 subjects, 20 iterations, 40 revolutions
Summary: (best [mean] worst) = 0.500 [1.250] 2.000 (s)
         ⅀T: 100 μRSD/r: 0.750%

run 3
Date:    2016-01-01T00:00:00+01:00
Branch:  branch3
Context: foo
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

        $this->application->setTerminalDimensions(100, 14);
        $this->questionHelper->ask(Argument::cetera())->shouldBeCalledTimes(1)->will(function () use ($output) {
            $output->writeln('paginate..');

            return '';
        });

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
Context: foo
Scale:   10 subjects, 20 iterations, 40 revolutions
Summary: (best [mean] worst) = 0.500 [1.250] 2.000 (s)
         ⅀T: 100 μRSD/r: 0.750%

run 2
Date:    2016-01-01T00:00:00+01:00
Branch:  branch2
Context: foo
Scale:   10 subjects, 20 iterations, 40 revolutions
paginate..
run 3
Date:    2016-01-01T00:00:00+01:00
Branch:  branch3
Context: foo
Scale:   10 subjects, 20 iterations, 40 revolutions
Summary: (best [mean] worst) = 0.500 [1.250] 2.000 (s)
         ⅀T: 100 μRSD/r: 0.750%


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
}
