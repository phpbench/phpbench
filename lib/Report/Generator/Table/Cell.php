<?php

namespace PhpBench\Report\Generator\Table;

use PhpBench\Report\Generator\Table\AdditionalValue;

final class Cell
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var array<AdditionalValue>
     */
    private $secondaryValues;

    /**
     * @param mixed $value
     */
    public function __construct($value, AdditionalValue ...$additionalValues)
    {
        $this->value = $value;
        $this->secondaryValues = $additionalValues;
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

    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function addSecondaryValue(AdditionalValue $additionalValue): void
    {
        $this->secondaryValues[] = $additionalValue;
    }
}
