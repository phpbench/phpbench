<?php

namespace PhpBench\Assertion;

use PhpBench\Registry\Registry;
use PhpBench\Assertion\Asserter;
use PhpBench\DependencyInjection\Container;

/**
 * @method \PhpBench\Assertion\Asserter getService()
 */
class AsserterRegistry extends Registry
{
    public function __construct(Container $container, string $defaultService = null)
    {
        parent::__construct('assertion', $container, $defaultService);
    }
}
