<?php

namespace PhpBench\Expression\Ast;

class StringNode extends ScalarValue
{
    public function __construct(private readonly string $string)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return $this->string;
    }
}
