<?php

namespace PhpBench\Expression\Ast;

class BooleanNode extends ScalarValue
{
    public function __construct(private readonly bool $value)
    {
    }

    public function value(): bool
    {
        return $this->value;
    }
}
