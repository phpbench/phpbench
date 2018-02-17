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

namespace PhpBench\Storage;

use PhpBench\Registry\Registry;
use Psr\Container\ContainerInterface;

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
