<?php

namespace PhpBench\Extension;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use PhpBench\Storage\Driver\Reports\CurlTransport;
use PhpBench\Storage\Driver\Reports\ReportsDriver;
use PhpBench\Storage\Driver\Reports\ReportClient;
use PhpBench\Serializer\ElasticEncoder;

class ReportsExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
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
            return new ReportClient(
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
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
        return [
            'storage.reports.connection' => [],
            'storage.reports.store_iterations' => false,
            'storage.reports.inner_driver' => 'xml',
            'storage.reports.api_key' => 'changeme',
        ];
    }
}
