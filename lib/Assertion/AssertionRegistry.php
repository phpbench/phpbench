<?php

namespace PhpBench\Assertion;

use PhpBench\Benchmark\Assertion;
use PhpBench\Registry\Registry;

/**
 * @method Assertion getService
 */
class AssertionRegistry extends Registry
{
    public function __construct(array $serviceMap, string $defaultService)
    {
        parent::__construct('assertion', $serviceMap, $defaultService);
    }
}
