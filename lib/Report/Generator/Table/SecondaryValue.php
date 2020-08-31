<?php

namespace PhpBench\Report\Generator\Table;

final class SecondaryValue
{
    /**
     */
    private $value;

    /**
     * @var string
     */
    private $role;

    /**
     */
    private function __construct($value, string $role)
    {
        $this->value = $value;
        $this->role = $role;
    }

    /**
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
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
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
