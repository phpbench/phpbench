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
     * @param array<string,array{type:string,value:string}> $parameters
     */
    public static function fromArray(array $parameters): self 
    {
        return new self(array_map(function (array $typeValuePair) {
            return ParameterContainer::fromTypeValuePair($typeValuePair);
        }, $parameters));
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
}
