<?php

namespace PhpBench\Report\Generator\Table;

final class Cell
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param mixed $value
     */
    public static function fromValue($value): self
    {
        return new self($value);
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}
