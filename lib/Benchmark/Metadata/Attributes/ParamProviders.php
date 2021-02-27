<?php

namespace PhpBench\Benchmark\Metadata\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class ParamProviders extends AbstractArrayAnnotation
{
    /**
     * @var string[]
     */
    public $providers;

    /**
     * @param string[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }
}
