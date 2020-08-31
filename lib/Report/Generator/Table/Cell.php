<?php

namespace PhpBench\Report\Generator\Table;

final class Cell
{
    /**
     */
    private $value;

    /**
     * @var array<SecondaryValue>
     */
    private $secondaryValues;

    /**
     */
    public function __construct($value, SecondaryValue ...$additionalValues)
    {
        $this->value = $value;
        $this->secondaryValues = $additionalValues;
    }

    /**
     */
    public static function fromValue($value): self
    {
        return new self($value);
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

    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @return array<SecondaryValue>
     */
    public function getSecondaryValues(): array
    {
        return $this->secondaryValues;
    }

    public function addSecondaryValue(SecondaryValue $additionalValue): void
    {
        $this->secondaryValues[] = $additionalValue;
    }
}
