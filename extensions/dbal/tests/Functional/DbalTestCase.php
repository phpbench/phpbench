<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\Dbal\Tests\Functional;

use Doctrine\DBAL\DriverManager;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\ConnectionManager;
use PhpBench\Tests\Functional\FunctionalTestCase;

class DbalTestCase extends FunctionalTestCase
{
    protected function getConnection()
    {
        return DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);
    }

    protected function getManager()
    {
        $manager = new ConnectionManager($this->getConnection());
        $manager->initializeSchema();

        return $manager;
    }
}
