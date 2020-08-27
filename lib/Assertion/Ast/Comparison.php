<?php

namespace PhpBench\Assertion\Ast;

class Comparison implements Node
{
    /**
     * @var Value
     */
    private $value1;
    /**
     * @var string
     */
    private $operator;
    /**
     * @var Value
     */
    private $value2;

    public function __construct(Value $value1, string $operator, Value $value2)
    {
        $this->value1 = $value1;
        $this->operator = $operator;
        $this->value2 = $value2;
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function value2(): Value
    {
        return $this->value2;
    }

    public function value1(): Value
    {
        return $this->value1;
    }
}
