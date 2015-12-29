<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Extensions\Blackfire;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;
use PhpBench\Extensions\Blackfire\Executor\BlackfireExecutor;

class BlackfireExtension implements ExtensionInterface
{
    public function configure(Container $container)
    {
        $container->register('benchmark.executor.blackfire', function (Container $container) {
            return new BlackfireExecutor(
                $container->get('benchmark.remote.launcher')
            );
        },array('executor' => array('name' => 'blackfire')));
    }

    public function build(Container $container)
    {
    }
}
