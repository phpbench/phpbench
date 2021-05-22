<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Expression\Exception\EvaluationError;

class DisplayAsNode extends NumberValue
{
    /**
     * @var UnitNode
     */
    private $as;

    /**
     * @var Node
     */
    private $value;

    /**
     * @var Node|null
     */
    private $precision;

    public function __construct(Node $value, UnitNode $as, ?Node $precision = null)
    {
        $this->as = $as;
        $this->value = $value;
        $this->precision = $precision;
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
                get_class($this->value)
            ));
        }

        return $this->value->value();
    }

    public function precision(): ?Node
    {
        return $this->precision;
    }
}
