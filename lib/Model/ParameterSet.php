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
     * @var Parameters
     */
    private $parameters;

    /**
     */
    private function __construct(string $name, Parameters $parameters)
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
        return $this->parameters->toArray();
    }

    /**
     * @param array<string, ParameterContainer> $parameterContainers
     */
    public static function fromContainers(string $name, array $parameterContainers): self
    {
        return new self($name, Parameters::fromContainers($parameterContainers));
    }

    /**
     * @param array<string,array{"type":string,"value":string}> $parameters
     */
    public static function fromUnsafeArray(string $name, array $parameters): ParameterSet
    {
        return new self($name, Parameters::fromUnsafeArray($parameters));
    }

    /**
     * @param array<string,mixed> $parameters
     */
    public static function fromArray(string $name, array $parameters): self
    {
        return new self($name, Parameters::fromArray($parameters));
    }

    /**
     * @return array<string,mixed>
     */
    public function toUnserializedArray(): array
    {
        return $this->parameters->toUnserializedArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(): array
    {
        return $this->parameters->toSerializedArray();
    }
}
