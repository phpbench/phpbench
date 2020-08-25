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

use PhpBench\Console\Command\Handler\SuiteCollectionHandler;
use PhpBench\Registry\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends Command
{
    private $collectionHandler;
    private $storage;

    public function __construct(
        SuiteCollectionHandler $collectionHandler,
        Registry $storage
    ) {
        parent::__construct();
        $this->collectionHandler = $collectionHandler;
        $this->storage = $storage;
    }

    public function configure()
    {
        $this->setName('delete');
        $this->setDescription('Delete suites from storage');
        $this->setHelp(<<<'EOT'
Delete one or many suites from storage:

    $ %command.full_name% --uuid=38aa53bf1edb2cf21391407487d98f37a2e0e2ba
EOT
        );
        SuiteCollectionHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $this->collectionHandler->suiteCollectionFromInput($input);

        $storage = $this->storage->getService();

        foreach ($collection as $suite) {
            $output->writeln('- ' . $suite->getUuid());
            $storage->delete($suite->getUuid());
        }

        return 0;
    }
}
