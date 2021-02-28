<?php

namespace PhpBench\Benchmark\Metadata\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class ParamProviders
{
    /**
     * @var string[]
     */
    public $providers;

    /**
     * @param string|string[] $providers
     */
    public function __construct(array | string $providers)
    {
        $this->providers = (array)$providers;
    }
}
