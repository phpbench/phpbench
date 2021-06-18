<?php

namespace PhpBench\Model;

final class Parameters
{
    /**
     * @var array<string,ParameterContainer>
     */
    private $parameters;

    /**
     * @param array<string,ParameterContainer> $parameters
     */
    private function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param array<string,array{"type":string,"value":string}> $parameters
     */
    public static function fromUnsafeArray(array $parameters): self
    {
        return new self(array_map(function (array $typeValuePair) {
            return ParameterContainer::fromTypeValuePair($typeValuePair);
        }, $parameters));
    }

    /**
     * @param array<string,mixed>[] $parameters
     */
    public static function fromArray(array $parameters): self
    {
        return new self(array_map(function ($parameter) {
            return ParameterContainer::fromValue($parameter);
        }, $parameters));
    }

    /**
     * @param array<stirng, self> $parameterContainers
     */
    public static function fromContainers(array $parameterContainers): self
    {
        return new self($parameterContainers);
    }

    /**
     * @return ParameterContainer[]
     */
    public function toArray(): array
    {
        return $this->parameters;
    }

    /**
     * @return array<string,mixed>
     */
    public function toUnserializedArray(): array
    {
        return array_map(function (ParameterContainer $container) {
            return $container->unwrap();
        }, $this->parameters);
    }

    /**
     * @return array<mixed,string>
     */
    public function toSerializedArray(): array
    {
        return array_map(function (ParameterContainer $container) {
            return $container->getValue();
        }, $this->parameters);
    }
}
