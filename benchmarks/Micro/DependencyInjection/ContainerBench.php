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

namespace PhpBench\Benchmarks\Micro\DependencyInjection;

use PhpBench\DependencyInjection\Container;

/**
 * @Iterations(10)
 * @Revs(10)
 * @OutputTimeUnit("milliseconds", precision=6)
 */
class ContainerBench
{
    public function benchInitNoExtensions()
    {
        $container = new Container();
        $container->init();
    }

    public function benchInitCoreExtension()
    {
        $container = new Container([
            'PhpBench\Extension\CoreExtension',
        ]);
        $container->init();
    }
}
