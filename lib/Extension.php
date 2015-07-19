<?php

namespace PhpBench;

use PhpBench\Container;

interface Extension
{
    public function configure(Container $container, $config);
}
