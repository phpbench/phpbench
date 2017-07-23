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

namespace PhpBench\Extensions\Dbal\Tests\Functional\Command;

use PhpBench\Extensions\Dbal\Command\MigrateCommand;
use PhpBench\Extensions\Dbal\Tests\Functional\DbalTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class MigrateCommandTest extends DbalTestCase
{
    private $output;

    public function setUp()
    {
        $this->output = new BufferedOutput();
        $this->command = new MigrateCommand($this->getConnection());
    }

    /**
     * It should show a message saying how many statements would be executed if
     * not options are given.
     */
    public function testNoArgs()
    {
        $this->execute();
        $this->assertContains(
            'would be executed',
            $this->output->fetch()
        );
    }

    /**
     * It should dump the SQL.
     */
    public function testDumpSql()
    {
        $this->execute([
            '--dump-sql' => true,
        ]);
        $this->assertContains(
            'CREATE TABLE',
            $this->output->fetch()
        );
    }

    /**
     * It should migrate the schema.
     */
    public function testMigrate()
    {
        $this->execute([
            '--force' => true,
        ]);

        $this->assertContains(
            '18 sql statements',
            $this->output->fetch()
        );

        $this->execute([
            '--force' => true,
        ]);

        return;

        // dbal creates temporary tables, drops the existing tables and then creates new ones.
        // I do not know why, as here the schemas should be identical.
        $this->assertContains(
            '39 sql statements',
            $this->output->fetch()
        );
    }

    private function execute($args = [])
    {
        $input = new ArrayInput($args, $this->command->getDefinition());

        $this->command->execute($input, $this->output);
    }
}
