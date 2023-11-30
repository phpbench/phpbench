<?php

namespace PhpBench\Config\Processor;

use Closure;
use PhpBench\Config\ConfigLoader;
use PhpBench\Config\ConfigProcessor;

class CallbackProcessor implements ConfigProcessor
{
    public function __construct(private readonly Closure $callback)
    {
    }

    public function process(ConfigLoader $loader, string $path, array $config): array
    {
        return $this->callback->call($this, $loader, $path, $config);
    }
}
