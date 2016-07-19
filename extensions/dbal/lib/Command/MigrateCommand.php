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

namespace PhpBench\Extensions\Dbal\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Comparator;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Schema;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends Command
{
    private $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    public function configure()
    {
        $this->setName('dbal:migrate');
        $this->setDescription('Migrate the database schema to the latest version');
        $this->setHelp(<<<'EOT'
This command will migrate the database schema to the latest version.

NOTE: This command does not *migrate* your data, only the schema. It is recommended that
      you first archive your benchmarks results with <info>phpbench archive</info> before migrating
      the schema.
EOT
    );

        $this->addOption('dump-sql', null, InputOption::VALUE_NONE, 'Dumps the generated SQL statements to the screen (does not execute them).');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Causes the generated SQL statements to be physically executed against your database.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $dumpSql = $input->getOption('dump-sql');
        $force = $input->getOption('force');

        $manager = $this->connection->getSchemaManager();
        $this->connection->connect();
        $fromSchema = $manager->createSchema();
        $toSchema = new Schema();
        $comparator = new Comparator();
        $schemaDiff = $comparator->compare($fromSchema, $toSchema);
        $sql = $schemaDiff->toSaveSql($this->connection->getDatabasePlatform());

        if (!$dumpSql && !$force) {
            $output->writeln(sprintf(
                '%d sql statements would be executed. Use `--dump-sql` to show them and `--force` to execute them on the database.',
                count($sql)
            ));

            return;
        }

        if ($dumpSql) {
            foreach ($sql as $line) {
                $output->writeln($line);
            }
        }

        if ($force) {
            $output->writeln('Updating database schema');
            foreach ($sql as $line) {
                $this->connection->exec($line);
            }
            $output->writeln(sprintf('%d sql statements executed.', count($sql)));
        }
    }
}
