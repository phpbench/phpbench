<?php

namespace PhpBench\Expression\Ast;

class ToleratedTrue implements Node, PhpValue
{
    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return true;
    }
}
