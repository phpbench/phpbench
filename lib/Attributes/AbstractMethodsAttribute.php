<?php

namespace PhpBench\Attributes;

abstract class AbstractMethodsAttribute
{
    /**
     * @var string[]
     */
    public $methods;

    /**
     * @param string|string[] $methods
     */
    public function __construct(string | array $methods)
    {
        $this->methods = (array)$methods;
    }
}
