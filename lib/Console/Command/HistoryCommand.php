<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Console\Command;

use PhpBench\Registry\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HistoryCommand extends Command
{
    private $storage;

    public function __construct(
        Registry $storage
    ) {
        parent::__construct();
        $this->storage = $storage;
    }

    public function configure()
    {
        $this->setName('history');
        $this->setDescription('List previously executed benchmark runs.');
        $this->setHelp(<<<'EOT'
Show a list of previously executed benchmark runs.

    $ %command.full_name%

NOTE: This is applicable only when a storage driver has been configured.

You may use the `--limit=x` option to limit the number of results (default 10).
EOT
    );
        $this->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit number of entries', 10);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = $input->getOption('limit');

        if ($limit) {
            $output->writeln(sprintf('<comment>Limit set to %s</comment>', $limit));
        }

        $output->writeln(sprintf(
            '<info>[</> %s<info> ]</>',
            implode(' <info>|</> ', [
                'Run UUID',
                'Date',
                'VCS Branch',
                'Context',
            ])
        ));
        $output->write(PHP_EOL);

        $entries = $this->storage->getService()->history();
        foreach ($entries as $index => $entry) {
            $output->write(sprintf(
                "%s\t%s\t%s\t%s\n",
                $entry->getRunId(),
                $entry->getDate()->format('Y-m-d H:i:s'),
                $entry->getVcsBranch(),
                $entry->getContext()
            ));

            if (null !== $limit) {
                if ($index > $limit) {
                    break;
                }
            }
        }
    }
}
