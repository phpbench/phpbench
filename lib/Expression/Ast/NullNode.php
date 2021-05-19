<?php

namespace PhpBench\Expression\Ast;

final class NullNode extends PhpValue
{
    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return null;
    }
}
