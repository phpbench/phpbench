<?php

namespace PhpBench\Model;

use PhpBench\Registry\Config;

class ResolvedExecutor
{
    public function __construct(private readonly string $name, private readonly Config $config)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public static function fromNameAndConfig(string $name, Config $executorConfig): ResolvedExecutor
    {
        return new self($name, $executorConfig);
    }
}
