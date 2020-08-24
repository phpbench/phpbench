<?php

namespace PhpBench\Report\Generator\Table;

final class SecondaryValue
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

    /**
     * @param mixed $value
     */
    public static function create($value, string $role): self
    {
        return new self($value, $role);
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

    public function getRole(): string
    {
        return $this->role;
    }
}
