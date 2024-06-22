<?php

namespace PhpBench\Reflection;

/**
 * @template T of object
 */
final class ReflectionAttribute
{
    /**
     * @param class-string<T> $name
     * @param mixed[] $args
     */
    public function __construct(public string $name, public array $args)
    {
    }

    /**
     * @return T|null
     */
    public function instantiate(): ?object
    {
        if (!class_exists($this->name)) {
            return null;
        }

        return new $this->name(...$this->args);
    }
}
