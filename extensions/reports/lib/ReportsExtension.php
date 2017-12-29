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

namespace PhpBench\Extensions\Reports;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\Extensions\Reports\Driver\ReportsClient;
use PhpBench\Extensions\Reports\Driver\ReportsDriver;
use PhpBench\Serializer\XmlEncoder;

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
                new XmlEncoder(),
                $container->getParameter('storage.reports.inner_driver')
            );
        }, ['storage_driver' => ['name' => 'reports']]);

        $container->register('storage.driver.reports.client', function (Container $container) {
            return new ReportsClient(
                $container->getParameter('storage.reports.api_key'),
                $container->getParameter('storage.reports.url')
            );
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return [
            'storage.reports.url' => null,
            'storage.reports.inner_driver' => 'xml',
            'storage.reports.api_key' => getenv('REPORTS_API_KEY') ?: 'changeme',
        ];
    }
}
