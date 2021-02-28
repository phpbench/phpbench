<?php

namespace PhpBench\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Executor
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var array<string,mixed>
     */
    public $config;

    /**
     * @param array<string,mixed> $config
     */
    public function __construct(string $name, array $config = [])
    {
        $this->name = $name;
        $this->config = $config;
    }
}
