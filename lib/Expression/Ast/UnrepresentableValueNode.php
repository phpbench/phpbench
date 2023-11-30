<?php

namespace PhpBench\Expression\Ast;

final class UnrepresentableValueNode extends PhpValue
{
    public function __construct(private readonly mixed $value)
    {
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }
}
