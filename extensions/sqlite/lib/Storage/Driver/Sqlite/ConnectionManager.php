<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\Sqlite\Storage\Driver\Sqlite;

use PhpBench\PhpBench;

class ConnectionManager
{
    private $dbPath;

    public function __construct($dbPath)
    {
        $this->dbPath = $dbPath;
    }

    public function getConnection()
    {
        $create = false;
        if (!file_exists($this->dbPath)) {
            $create = true;
        }

        $conn = new \PDO('sqlite:' . $this->dbPath);
        $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $conn->sqliteCreateFunction('regexp', function ($expr, $string) {
            return preg_match('{' . $expr . '}', $string);
        }, 2);

        if ($create) {
            $this->createDatabase($conn);
        }

        return $conn;
    }

    private function createDatabase(\PDO $conn)
    {
        $schema = file_get_contents(__DIR__ . '/sqlite.sql');
        $conn->exec($schema);
        $conn->exec(sprintf(
            'INSERT INTO version (date, phpbench_version) VALUES ("%s", "%s")',
            date('c'),
            PhpBench::VERSION
        ));
    }
}
