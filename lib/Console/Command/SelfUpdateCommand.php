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

use Humbug\SelfUpdate\Updater;
use PhpBench\PhpBench;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command
{
    private $updater;

    public function __construct(Updater $updater = null)
    {
        parent::__construct();
        $this->updater = $updater;
    }

    public function configure()
    {
        $this->setName('self-update');
        $this->setDescription('Update the application to the latest version.');
        $this->addOption('rollback', null, InputOption::VALUE_NONE, 'Rollback to previous version.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // lazily instantiate the updater because otherwise it requires root
        // privleges when instantaited when the PHAR is in a non-writable
        // directory.
        if (!$this->updater) {
            $this->updater = new Updater();
            $this->updater->getStrategy()->setPharUrl(PhpBench::PHAR_URL);
            $this->updater->getStrategy()->setVersionUrl(PhpBench::PHAR_VERSION_URL);
        }

        if ($input->getOption('rollback')) {
            return $this->doRollback($output);
        } else {
            return $this->doUpdate($output);
        }
    }

    private function doUpdate(OutputInterface $output)
    {
        $result = $this->updater->update();

        if (!$result) {
            $output->writeln('No update required, PHPBench is already at the latest version.');

            return 0;
        }

        $output->writeln(sprintf(
            'PHPBench was updated from "%s" to "%s"',
            $this->updater->getOldVersion(),
            $this->updater->getNewVersion()
        ));

        return 0;
    }

    private function doRollback(OutputInterface $output)
    {
        $result = $this->updater->rollback();

        if (!$result) {
            throw new \RuntimeException(
                'Could not rollback.'
            );
        }

        $output->writeln('Successfully rolled back.');

        return 0;
    }
}
