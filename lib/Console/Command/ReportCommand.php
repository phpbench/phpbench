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

use PhpBench\Console\Command\Handler\DumpHandler;
use PhpBench\Console\Command\Handler\ReportHandler;
use PhpBench\Console\Command\Handler\SuiteCollectionHandler;
use PhpBench\Console\Command\Handler\TimeUnitHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportCommand extends Command
{
    /**
     * @var ReportHandler
     */
    private $reportHandler;

    /**
     * @var TimeUnitHandler
     */
    private $timeUnitHandler;

    /**
     * @var SuiteCollectionHandler
     */
    private $collectionHandler;

    /**
     * @var DumpHandler
     */
    private $dumpHandler;

    public function __construct(
        ReportHandler $reportHandler,
        TimeUnitHandler $timeUnitHandler,
        SuiteCollectionHandler $collectionHandler,
        DumpHandler $dumpHandler
    ) {
        parent::__construct();
        $this->reportHandler = $reportHandler;
        $this->timeUnitHandler = $timeUnitHandler;
        $this->collectionHandler = $collectionHandler;
        $this->dumpHandler = $dumpHandler;
    }

    public function configure()
    {
        $this->setName('report');
        $this->setDescription('Generate a report from storage or an XML file');
        $this->setHelp(<<<'EOT'
Generate report from the latest stored suite:

    $ %command.full_name% --uuid=latest --report=aggregate

Generate report from a tag:

    $ %command.full_name% --uuid=tag:foobar --report=aggregate

Generate report from a UUID (as determined from the <info>log</info> command:

    $ %command.full_name% --uuid=133a2605fac74edabf046edeb9c5f7c4dc1a3aac --report=aggregate

Generate from an XML file:

    $ %command.full_name% --file=dump.xml

To dump an XML file, use the <info>run</info> command with the
<comment>dump-file</comment> option.

You can specify the options multiple times to compare multiple suites.
EOT
        );
        ReportHandler::configure($this);
        TimeUnitHandler::configure($this);
        SuiteCollectionHandler::configure($this);
        DumpHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('report')) {
            throw new \InvalidArgumentException(
                'You must specify or configure at least one report, e.g.: --report=default'
            );
        }

        $this->timeUnitHandler->timeUnitFromInput($input);
        $collection = $this->collectionHandler->suiteCollectionFromInput($input);
        $this->dumpHandler->dumpFromInput($input, $output, $collection);
        $this->reportHandler->reportsFromInput($input, $output, $collection);

        return 0;
    }
}
