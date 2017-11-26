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

namespace PhpBench\Extension;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Serializer\ElasticEncoder;
use PhpBench\Storage\Driver\Reports\CurlTransport;
use PhpBench\Storage\Driver\Reports\ReportsClient;
use PhpBench\Storage\Driver\Reports\ReportsDriver;

class ReportsExtension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(Container $container)
    {
        $container->register('storage.driver.reports', function (Container $container) {
            return new ReportsDriver(
                $container->get('storage.driver.reports.client'),
                $container->get('storage.driver_registry'),
                $container->getParameter('storage.reports.inner_driver')
            );
        }, ['storage_driver' => ['name' => 'reports']]);

        $container->register('storage.driver.reports.client', function (Container $container) {
            return new ReportsClient(
                $container->get('storage.driver.reports.transport'),
                new ElasticEncoder(),
                $container->getParameter('storage.reports.store_iterations')
            );
        });

        $container->register('storage.driver.reports.transport', function (Container $container) {
            return new CurlTransport(
                $container->getParameter('storage.reports.connection'),
                $container->getParameter('storage.reports.api_key')
            );
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return [
            'storage.reports.connection' => [],
            'storage.reports.store_iterations' => false,
            'storage.reports.inner_driver' => 'xml',
            'storage.reports.api_key' => getenv('REPORTS_API_KEY') ?: 'changeme',
        ];
    }
}
