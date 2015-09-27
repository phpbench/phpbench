<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\DependencyInjection;

interface ExtensionInterface
{
    /**
     * Register services with the container.
     *
     * @param Container $container
     */
    public function configure(Container $container);

    /**
     * Called after all services in all extensions have been registered.
     *
     * @param Container $container
     */
    public function build(Container $container);
}
