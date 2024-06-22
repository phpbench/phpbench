<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Expression\Exception\EvaluationError;

class DisplayAsNode extends NumberValue
{
    public function __construct(private readonly Node $value, private readonly UnitNode $as, private readonly ?Node $precision = null)
    {
    }

    public function node(): Node
    {
        return $this->value;
    }

    public function as(): UnitNode
    {
        return $this->as;
    }

    /**
     * {@inheritDoc}
     */
    public function value()
    {
        if (!$this->value instanceof NumberNode) {
            throw new EvaluationError($this, sprintf(
                'Expected display at value to have been evaluated to a number node, but its a "%s"',
                $this->value::class
            ));
        }

        return $this->value->value();
    }

    public function precision(): ?Node
    {
        return $this->precision;
    }
}
