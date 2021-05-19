<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Expression\Exception\EvaluationError;

class PercentageNode extends NumberNode
{
    /**
     * @var Node
     */
    private $value;

    public function __construct(Node $value)
    {
        $this->value = $value;
    }

    public function valueNode(): Node
    {
        return $this->value;
    }

    public function value(): float
    {
        $value = $this->value;

        if (!$value instanceof FloatNode && !$value instanceof IntegerNode) {
            throw new EvaluationError($this, sprintf(
                'Percentage node has not been evaluated, its value is a "%s"',
                get_class($this->value)
            ));
        }

        return (float)$value->value();
    }
}
