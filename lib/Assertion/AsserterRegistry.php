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

namespace PhpBench\Assertion;

use PhpBench\DependencyInjection\Container;
use PhpBench\Registry\Registry;

/**
 * @method \PhpBench\Assertion\Asserter getService(string $name)
 */
class AsserterRegistry extends Registry
{
    const NAME = 'assertion';

    public function __construct(Container $container, string $defaultService = null)
    {
        parent::__construct(self::NAME, $container, $defaultService);
    }
}
