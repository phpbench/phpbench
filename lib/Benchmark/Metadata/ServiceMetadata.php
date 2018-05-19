<?php

namespace PhpBench\Benchmark\Metadata;

class ServiceMetadata
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options;

    public function __construct(string $name, array $options)
    {
        $this->name = $name;
        $this->options = $options;
    }
}
