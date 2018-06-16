<?php

namespace PhpBench\Runner;

interface Sampler
{
    public function sample(): Data;
}
