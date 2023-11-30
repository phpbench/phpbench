<?php

namespace PhpBench\Benchmark\Metadata;

class ExecutorMetadata
{
    public function __construct(private readonly string $name, private readonly array $config)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getRegistryConfig(): array
    {
        return array_merge($this->config, [
            'executor' => $this->getName(),
        ]);
    }
}
