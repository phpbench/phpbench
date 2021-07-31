<?php

namespace PhpBench\Model;

use Exception;
use RuntimeException;

final class ParameterContainer
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public static function fromValue($value): self
    {
        try {
            @$serialized = @serialize($value);
        } catch (Exception $error) {
            throw new RuntimeException(sprintf('Cannot serialize value: %s', $error->getMessage()));
        }

        return new self($serialized);
    }

    public static function fromSerializedValue(string $serializedValue): self
    {
        return new self($serializedValue);
    }

    /**
     * @return mixed
     */
    public function toUnserializedValue()
    {
        return unserialize($this->value);
    }
}
