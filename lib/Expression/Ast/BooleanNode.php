<?php

namespace PhpBench\Expression\Ast;

class BooleanNode extends PhpValue
{
    public function __construct(private readonly bool $value)
    {
    }

    public function value(): bool
    {
        return $this->value;
    }
}
