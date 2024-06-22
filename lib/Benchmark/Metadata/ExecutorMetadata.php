<?php

namespace PhpBench\Benchmark\Metadata;

class ExecutorMetadata
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(private readonly string $name, private readonly array $config)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRegistryConfig(): array
    {
        return array_merge($this->config, [
            'executor' => $this->getName(),
        ]);
    }
}
