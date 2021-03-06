<?php

namespace PhpBench\Model;

use Exception;
use RuntimeException;

final class ParameterContainer
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $value;

    public function __construct(string $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function getType(): string
    {
        return $this->type;
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

        return new self(gettype($value), $serialized);
    }

    /**
     * Create a value from the (get)type and serialized value returned from a
     * remote process.
     *
     * ```
     * ParameterContainer::fromTypeValuePair(['type' => gettype($value), 'value' => serialize($value)])
     * ```
     *
     * @param array{"type":string,"value":string} $typeValue
     */
    public static function fromWrappedValue(array $typeValue): self
    {
        if (!isset($typeValue['type'])) {
            throw new RuntimeException(sprintf(
                '`type` key not set in type-value pair, got "%s"',
                json_encode($typeValue)
            ));
        }

        if (!isset($typeValue['value'])) {
            throw new RuntimeException(sprintf(
                '`value` key not set in type-value pair, got "%s"',
                json_encode($typeValue)
            ));
        }

        return new self($typeValue['type'], $typeValue['value']);
    }

    /**
     * @return mixed
     */
    public function toUnwrappedValue()
    {
        return unserialize($this->value);
    }
}
