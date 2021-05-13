<?php

namespace PhpBench\Expression\Ast;

class PropertyAccessNode implements Node
{
    /**
     * @var Node[]
     */
    private $segments;

    public function __construct(array $segments)
    {
        $this->segments = $segments;
    }

    /**
     * @return Node[]
     */
    public function segments(): array
    {
        return $this->segments;
    }
}
