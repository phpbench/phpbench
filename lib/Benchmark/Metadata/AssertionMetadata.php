<?php

namespace PhpBench\Benchmark\Metadata;

class AssertionMetadata
{
    private $expression;

    /**
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    public function __toString()
    {
        return $this->expression;
    }
}
