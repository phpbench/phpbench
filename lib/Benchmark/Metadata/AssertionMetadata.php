<?php

namespace PhpBench\Benchmark\Metadata;

class AssertionMetadata
{
    /**
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        $this->options = $options;

    }

    public function getOptions(): array
    {
        return $this->options;
    }
}

