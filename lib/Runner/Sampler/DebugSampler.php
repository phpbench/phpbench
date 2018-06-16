<?php

namespace PhpBench\Runner\Sampler;

use PhpBench\Runner\Data;
use PhpBench\Runner\Sampler;

class DebugSampler implements Sampler
{
    public function sample(array $parameters): Data
    {
        return new Data([]);
    }
}
