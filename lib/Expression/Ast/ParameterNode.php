<?php

namespace PhpBench\Expression\Ast;

class ParameterNode implements Node
{
    /**
     * @var array
     */
    private $segments;

    public function __construct(array $segments)
    {
        $this->segments = $segments;
    }

    public function segments(): array
    {
        return $this->segments;
    }
}
