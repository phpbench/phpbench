<?php

namespace PhpBench\Expression\Ast;

final class NullNode implements PhpValue
{
    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return null;
    }
}
