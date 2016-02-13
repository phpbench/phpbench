<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\Sqlite;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;

class SqliteExtension implements ExtensionInterface
{
    public function getDefaultConfig()
    {
        return array(
            'storage.sqlite.db_path' => '.phpbench.sqlite',
        );
    }

    public function load(Container $container)
    {
        $container->register('storage.driver.sqlite', function (Container $container) {
            return new Storage\Driver\SqliteDriver(
                $container->get('storage.driver.sqlite.loader'),
                $container->get('storage.driver.sqlite.persister'),
                $container->get('storage.driver.sqlite.repository')
            );
        }, array('storage_driver' => array('name' => 'sqlite')));
        $container->register('storage.driver.sqlite.connection_manager', function (Container $container) {
            return new Storage\Driver\Sqlite\ConnectionManager($container->getParameter('storage.sqlite.db_path'));
        });
        $container->register('storage.driver.sqlite.persister', function (Container $container) {
            return new Storage\Driver\Sqlite\Persister(
                $container->get('storage.driver.sqlite.connection_manager')
            );
        });
        $container->register('storage.driver.sqlite.loader', function (Container $container) {
            return new Storage\Driver\Sqlite\Loader(
                $container->get('storage.driver.sqlite.repository')
            );
        });
        $container->register('storage.driver.sqlite.constraint_visitor', function (Container $container) {
            return new Storage\Driver\Sqlite\ConstraintVisitor();
        });
        $container->register('storage.driver.sqlite.repository', function (Container $container) {
            return new Storage\Driver\Sqlite\Repository(
                $container->get('storage.driver.sqlite.connection_manager'),
                $container->get('storage.driver.sqlite.constraint_visitor')
            );
        });
    }

    public function build(Container $container)
    {
    }
}
