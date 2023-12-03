<?php

namespace PhpBench\Expression\Ast;

class LabelNode extends PhpValue
{
    public function __construct(private readonly PhpValue $label)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return $this->label->value();
    }
}
