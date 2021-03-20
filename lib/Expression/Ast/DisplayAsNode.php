<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Expression\Exception\EvaluationError;

class DisplayAsNode implements NumberValue
{
    /**
     * @var UnitNode
     */
    private $as;

    /**
     * @var Node
     */
    private $value;

    public function __construct(Node $value, UnitNode $as)
    {
        $this->as = $as;
        $this->value = $value;
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
}
