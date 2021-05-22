<?php

namespace PhpBench\Expression\Ast;

final class ToleratedTrue extends PhpValue
{
    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return true;
    }
}
