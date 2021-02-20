<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Expression\Evaluator\PhpValue;

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
