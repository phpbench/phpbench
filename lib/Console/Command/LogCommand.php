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

namespace PhpBench\Console\Command;

use PhpBench\Console\Application;
use PhpBench\Console\CharacterReader;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use PhpBench\Registry\Registry;
use PhpBench\Util\TimeUnit;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class LogCommand extends Command
{
    private $storage;
    private $timeUnit;
    private $timeUnitHandler;
    private $characterReader;

    public function __construct(
        Registry $storage,
        TimeUnit $timeUnit,
        TimeUnitHandler $timeUnitHandler,
        CharacterReader $characterReader = null
    ) {
        parent::__construct();
        $this->storage = $storage;
        $this->timeUnitHandler = $timeUnitHandler;
        $this->timeUnit = $timeUnit;
        $this->characterReader = $characterReader ?: new CharacterReader();
    }

    public function configure()
    {
        $this->setName('log');
        $this->setDescription('List previously executed and stored benchmark runs.');
        $this->setHelp(<<<'EOT'
Show a list of previously executed benchmark runs.

    $ %command.full_name%

NOTE: This is only possible when a storage driver has been configured.
EOT
    );
        // allow common time unit options
        TimeUnitHandler::configure($this);

        $this->addOption('no-pagination', 'P', InputOption::VALUE_NONE, 'Do not paginate');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->timeUnitHandler->timeUnitFromInput($input);
        $paginate = false === $input->getOption('no-pagination');

        // if we have an application, get the terminal dimensions, if the
        // terminal dimensions are null then set the height to the arbitrary
        // value of 100.
        $height = 100;

        /** @var Application|null $application */
        $application = $this->getApplication();

        if ($application) {
            $height = (new Terminal())->getHeight();
            $height = $height ?: 100;
        }

        $height -= 1; // reduce height by one to accommodate the pagination prompt
        $nbRows = 0;
        $totalRows = 0;

        foreach ($this->storage->getService()->history() as $entry) {
            $lines = [];
            $lines[] = sprintf('<comment>run %s</>', $entry->getRunId());
            $lines[] = sprintf('Date:    ' . $entry->getDate()->format('c'));
            $lines[] = sprintf('Branch:  ' . $entry->getVcsBranch());
            $lines[] = sprintf('Tag:     ' . ($entry->getTag() ?: '<none>'));
            $lines[] = sprintf('Scale:   ' . '%d subjects, %d iterations, %d revolutions', $entry->getNbSubjects(), $entry->getNbIterations(), $entry->getNbRevolutions());

            $lines[] = sprintf(
                'Summary: (best [mean] worst) = %s [%s] %s (%s)',
                number_format($this->timeUnit->toDestUnit($entry->getMinTime()), 3),
                number_format($this->timeUnit->toDestUnit($entry->getMeanTime()), 3),
                number_format($this->timeUnit->toDestUnit($entry->getMaxTime()), 3),
                $this->timeUnit->getDestSuffix()
            );

            $lines[] = sprintf(
                '         ⅀T: %s μRSD/r: %s%%',
                $this->timeUnit->format($entry->getTotalTime(), null, TimeUnit::MODE_TIME),
                number_format($entry->getMeanRelStDev(), 3)
            );
            $lines[] = '';

            $nbRows = $this->writeLines($output, $nbRows, $height, $lines);

            // if pagination is diabled, then just pretend that the console height
            // is always greater than the number of rows.
            if (false === $paginate) {
                $height += $nbRows;
            }

            if ($paginate && $nbRows >= $height) {
                $output->write(sprintf(
                    '<question>lines %s-%s any key to continue, <q> to quit</question>',
                    $totalRows, $totalRows + $nbRows
                ));
                $character = $this->characterReader->read();

                if ($character == 'q') {
                    break;
                }
                $output->write(PHP_EOL);

                $totalRows += $nbRows;
                $nbRows = 0;
            }
        }

        return 0;
    }

    private function writeLines($output, $nbRows, $height, $lines)
    {
        $limit = count($lines);

        // if the output will exceed the height of the terminal
        if ($nbRows + $limit > $height) {
            // set the limit to the different and subtract one (for the prompt)
            $limit = $height - $nbRows;
        }

        for ($i = 0; $i < $limit; $i++) {
            $line = $lines[$i];
            $output->writeln($line);
            $nbRows++;
        }

        return $nbRows;
    }
}
