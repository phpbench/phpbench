<?php

namespace PhpBench\Model;

use Exception;
use RuntimeException;

final class ParameterContainer
{
    public function __construct(private readonly string $value)
    {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function fromValue(mixed $value): self
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
