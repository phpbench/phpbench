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

namespace PhpBench\Extensions\Dbal\Storage\Driver\Dbal;

use Doctrine\DBAL\Connection;
use PhpBench\PhpBench;

class ConnectionManager
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        $params = $this->connection->getParams();

        if ($params['driver'] == 'pdo_sqlite') {
            if ((isset($params['path']) && !file_exists($params['path']))) {
                $this->initializeSchema();
            }

            $this->initSqlite();
        }

        return $this->connection;
    }

    private function initSqlite()
    {
        // enable foreign key support
        $this->connection->exec('PRAGMA foreign_keys = ON');

        $params = $this->connection->getParams();

        if (!isset($params['path'])) {
            return;
        }

        $this->connection->getWrappedConnection()->sqliteCreateFunction('regexp', function ($expr, $string) {
            return preg_match('{' . $expr . '}', $string);
        }, 2);
    }

    public function initializeSchema()
    {
        $schema = new Schema();
        $statements = $schema->toSql($this->connection->getDriver()->getDatabasePlatform());

        foreach ($statements as $statement) {
            $this->connection->exec($statement);
        }

        $this->connection->exec(sprintf(
            'INSERT INTO version (date, phpbench_version) VALUES ("%s", "%s")',
            date('c'),
            PhpBench::VERSION
        ));
    }
}
