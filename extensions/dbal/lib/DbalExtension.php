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

namespace PhpBench\Extensions\Dbal;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Version;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Extensions\Dbal\Command\MigrateCommand;
use PhpBench\PhpBench;

class DbalExtension implements ExtensionInterface
{
    public function getDefaultConfig()
    {
        return [
            'storage.dbal.connection' => [
                'driver' => 'pdo_sqlite',
                'path' => '.phpbench.sqlite',
            ],
        ];
    }

    public function load(Container $container)
    {
        if (!class_exists(Version::class)) {
            throw new \RuntimeException(
                'The DBAL extension requires the "doctrine/dbal" package. Run `composer require --dev "doctrine/dbal"`'
            );
        }

        $container->register('storage.driver.dbal.connection', function (Container $container) {
            static $connection;

            if ($connection) {
                return $connection;
            }

            $params = $container->getParameter('storage.dbal.connection');

            if (isset($params['path'])) {
                $params['path'] = PhpBench::normalizePath($params['path']);
            }

            $connection = DriverManager::getConnection($params);

            return $connection;
        });

        $container->register('storage.driver.dbal.connection_manager', function (Container $container) {
            return new Storage\Driver\Dbal\ConnectionManager($container->get('storage.driver.dbal.connection'));
        });

        $container->register('storage.driver.dbal', function (Container $container) {
            return new Storage\Driver\DbalDriver(
                $container->get('storage.driver.dbal.loader'),
                $container->get('storage.driver.dbal.persister'),
                $container->get('storage.driver.dbal.repository')
            );
        }, ['storage_driver' => ['name' => 'dbal']]);

        $container->register('storage.driver.dbal.persister', function (Container $container) {
            return new Storage\Driver\Dbal\Persister(
                $container->get('storage.driver.dbal.connection_manager')
            );
        });

        $container->register('storage.driver.dbal.loader', function (Container $container) {
            return new Storage\Driver\Dbal\Loader(
                $container->get('storage.driver.dbal.repository')
            );
        });

        $container->register('storage.driver.dbal.visitor.token_value', function (Container $container) {
            return new Storage\Driver\Dbal\Visitor\TokenValueVisitor(
                $container->get('storage.uuid_resolver')
            );
        });

        $container->register('storage.driver.dbal.repository', function (Container $container) {
            return new Storage\Driver\Dbal\Repository(
                $container->get('storage.driver.dbal.connection_manager'),
                $container->get('storage.driver.dbal.visitor.token_value')
            );
        });

        $container->register('command.dbal.migrate', function (Container $container) {
            return new MigrateCommand($container->get('storage.driver.dbal.connection'));
        }, ['console.command' => []]);
    }
}
