<?php

namespace PhpBench\Runner\Stage;

use PhpBench\Runner\Stage;

class SamplerStage implements Stage
{
    /**
     * @var Sampler
     */
    private $sampler;
    /**
     * @var array
     */
    private $config;

    public function __construct(Sampler $sampler, array $config)
    {
        $this->sampler = $sampler;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        while (true) {
            yield $this->sampler->sample($config);
        }
    }
}
