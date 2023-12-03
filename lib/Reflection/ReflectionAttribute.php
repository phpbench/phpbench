<?php

namespace PhpBench\Reflection;

final class ReflectionAttribute
{
    /**
     * @param mixed[] $args
     */
    public function __construct(public string $name, public array $args)
    {
    }

    /**
     * @return ?object
     */
    public function instantiate()
    {
        return (function (string $name) {
            if (!class_exists($name)) {
                return null;
            }

            return new $name(...$this->args);
        })($this->name);
    }
}
