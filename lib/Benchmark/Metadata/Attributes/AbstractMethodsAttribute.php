<?php

namespace PhpBench\Benchmark\Metadata\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
abstract class AbstractMethodsAttribute
{
    /**
     * @var string[]
     */
    public $methods;

    /**
     * @param string|string[] $methods
     */
    public function __construct(string|array $methods)
    {
        $this->methods = (array)$methods;
    }
}
