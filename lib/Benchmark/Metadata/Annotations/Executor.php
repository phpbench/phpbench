<?php

namespace PhpBench\Benchmark\Metadata\Annotations;

/**
 * @Annotation
 *
 * @Taget({"METHOD", "CLASS"})
 */
class Executor
{
    private string $name;
    /** @var array<string, mixed> */
    private array $config;

    /**
     * @param array{value: string} $params
     */
    public function __construct($params)
    {
        $this->name = $params['value'];
        unset($params['value']);

        $this->config = $params;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
