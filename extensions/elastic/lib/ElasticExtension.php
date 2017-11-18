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

class ElasticExtension implements ExtensionInterface
{
    public function getDefaultConfig()
    {
        return [
            'storage.elastic.connection' => [
                'scheme' => 'http',
                'host' => 'localhost',
                'port' => 9200,
                'index' => 'phpbench',
                'type' => 'suite',
            ],
        ];
    }

    public function load(Container $container)
    {
        $container->register('serializer.encoder.document', function (Container $container) {
            return new DocumentEncoder();
        });

        $container->register('storage.driver.elastic', function (Container $container) {
            return new ElasticDriver(
                $container->get('storage.elastic.client'),
                $container->get('serializer.encoder.document')
            );
        }, ['storage_driver' => ['name' => 'elastic']]);

        $container->register('storage.elastic.client', function (Container $container) {
            return new ElasticClient($container->getParameter('storage.elastic.connection'));
        });

    }
}
