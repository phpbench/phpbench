<?php

namespace PhpBench\Reflection;

final class ReflectionAttribute
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var mixed[]
     */
    public $args;

    /**
     * @param mixed[] $args
     */
    public function __construct(string $name, array $args)
    {
        $this->name = $name;
        $this->args = $args;
    }
}
