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

namespace PhpBench\Extensions\Elastic;

use PhpBench\PhpBench;
use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Extensions\Elastic\Driver\ElasticDriver;
use PhpBench\Extensions\Elastic\Driver\ElasticClient;
use PhpBench\Extensions\Elastic\Encoder\DocumentEncoder;
use PhpBench\Extensions\Elastic\Command\InstallCommand;

class ElasticExtension implements ExtensionInterface
{
    const PARAM_INNER_STORAGE = 'storage.elastic.inner_storage';
    const PARAM_CONNECTION = 'storage.elastic.connection';
    const PARAM_STORE_ITERATIONS = 'storage.elastic.store_iterations';

    public function getDefaultConfig()
    {
        return [
            self::PARAM_CONNECTION => [
                'scheme' => 'http',
                'host' => 'localhost',
                'port' => 9200,
                'index' => 'phpbench',
                'type' => 'suite',
            ],
            self::PARAM_INNER_STORAGE => 'xml',
            self::PARAM_STORE_ITERATIONS => false,
        ];
    }

    public function load(Container $container)
    {
        $container->register('serializer.encoder.document', function (Container $container) {
            return new DocumentEncoder();
        });

        $container->register('storage.driver.elastic', function (Container $container) {
            $innerStorage = $container->get('storage.driver_registry')->getService(
                $container->getParameter(self::PARAM_INNER_STORAGE)
            );

            return new ElasticDriver(
                $container->get('storage.elastic.client'),
                $innerStorage,
                $container->get('serializer.encoder.document'),
                self::PARAM_STORE_ITERATIONS
            );
        }, ['storage_driver' => ['name' => 'elastic']]);

        $container->register('storage.elastic.client', function (Container $container) {
            return new ElasticClient($container->getParameter(self::PARAM_CONNECTION));
        });

        $container->register('storage.elastic.command.update_mapping', function (Container $container) {
            return new InstallCommand($container->get('storage.elastic.client'));
        }, ['console.command' => []]);

    }
}
