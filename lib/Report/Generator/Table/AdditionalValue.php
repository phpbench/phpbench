<?php

namespace PhpBench\Report\Generator\Table;

use RuntimeException;

final class AdditionalValue
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var string
     */
    private $role;

    /**
     * @param mixed $value
     */
    private function __construct($value, string $role)
    {
        $this->value = $value;
        $this->role = $role;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}
