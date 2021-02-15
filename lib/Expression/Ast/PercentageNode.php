<?php

namespace PhpBench\Expression\Ast;

class PercentageNode implements Node
{
    /**
     * @var Node
     */
    private $value;

    public function __construct(Node $value)
    {
        $this->value = $value;
    }

    public function value(): Node
    {
        return $this->value;
    }
}
