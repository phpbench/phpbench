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

namespace PhpBench\Extensions\Dbal\Tests\Functional;

use Doctrine\DBAL\DriverManager;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\ConnectionManager;
use PhpBench\Tests\Functional\FunctionalTestCase;

class DbalTestCase extends FunctionalTestCase
{
    private $connection;
    private $manager;

    protected function getConnection()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $this->connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        return $this->connection;
    }

    protected function getManager()
    {
        if ($this->manager) {
            return $this->manager;
        }

        $this->manager = new ConnectionManager($this->getConnection());
        $this->manager->initializeSchema();

        return $this->manager;
    }

    protected function sqlQuery($sql)
    {
        $conn = $this->getManager()->getConnection();

        return $conn->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected function sqlCount($sql)
    {
        return count($this->sqlQuery($sql));
    }
}
