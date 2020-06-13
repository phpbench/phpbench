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

use PhpBench\Registry\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Archive and restore from and to storage.
 */
class ArchiveCommand extends Command
{
    private $archiver;

    public function __construct(
        Registry $archiver
    ) {
        parent::__construct();
        $this->archiver = $archiver;
    }

    public function configure()
    {
        $this->setName('archive');
        $this->setDescription('Archives and restore suites from and to storage.');
        $this->setHelp(<<<'EOT'
This command will dump (archive) or restore from an archive from and to the
configured storage driver.

Archive contents of Storage:

    $ %command.full_name%

Restore from archive to Storage:

    $ %command.full_name% --restore

Existing entries on both operations will be skipped.
EOT
    );
        $this->addOption('restore', null, InputOption::VALUE_NONE, 'Restore the archive');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $restore = $input->getOption('restore');

        if ($restore) {
            $this->archiver->getService()->restore($output);

            return 0;
        }

        $this->archiver->getService()->archive($output);

        return 0;
    }
}
