<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Model;

final class ParameterSet
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array<string,ParameterContainer>
     */
    private $parameters;

    /**
     * @param array<string,ParameterContainer> $parameters
     */
    private function __construct(string $name, array $parameters)
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @deprecated use getName instead
     */
    public function getIndex(): string
    {
        return $this->name;
    }

    /**
     * @return array<string,ParameterContainer>
     */
    public function toArray(): array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, ParameterContainer> $parameterContainers
     */
    public static function fromParameterContainers(string $name, array $parameterContainers): self
    {
        return new self($name, $parameterContainers);
    }

    /**
     * @param array<string,array<string,string>> $parameters
     */
    public static function fromSerializedParameters(string $name, array $parameters): ParameterSet
    {
        return new self($name, array_map(function (string $serializedValue) {
            return ParameterContainer::fromSerializedValue($serializedValue);
        }, $parameters));
    }

    /**
     * @param array<string,mixed> $parameters
     */
    public static function fromUnwrappedParameters(string $name, array $parameters): self
    {
        return new self($name, array_map(function ($parameter) {
            return ParameterContainer::fromValue($parameter);
        }, $parameters));
    }

    /**
     * @return array<string,mixed>
     */
    public function toUnwrappedParameters(): array
    {
        return array_map(function (ParameterContainer $container) {
            return $container->toUnwrappedValue();
        }, $this->parameters);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSerializedParameters(): array
    {
        return array_map(function (ParameterContainer $container) {
            return $container->getValue();
        }, $this->parameters);
    }
}
