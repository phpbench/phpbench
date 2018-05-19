<?php

namespace PhpBench\Benchmark\Metadata\Annotations;

/**
 * @Annotation
 * @Taget({"METHOD", "CLASS"})
 */
class Executor
{
    private $name;
    private $config;

    public function __construct($params)
    {
        $this->name = $params['value'];
        unset($params['value']);

        $this->config = $params ?? [];
        $this->config['executor'] = $this->name;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
