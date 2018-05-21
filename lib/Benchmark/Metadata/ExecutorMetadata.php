<?php

namespace PhpBench\Benchmark\Metadata;

class ExecutorMetadata
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $config;

    public function __construct(string $name, array $options)
    {
        $this->name = $name;
        $this->config = $options;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getRegistryConfig()
    {
        return array_merge($this->config, [
            'executor' => $this->getName(),
        ]);
    }
}
