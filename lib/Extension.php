<?php

namespace PhpBench;

use PhpBench\Container;

interface Extension
{
    /**
     * Register services with the container
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
