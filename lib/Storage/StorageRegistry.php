<?php

namespace PhpBench\Storage;

use PhpBench\Registry\Registry;
use Psr\Container\ContainerInterface;
use PhpBench\Storage\DriverInterface;

/**
 * @method DriverInterface getService()
 */
class StorageRegistry extends Registry
{
    public function __construct(ContainerInterface $container, string $default)
    {
        parent::__construct('storage', $container, $default);
    }
}
