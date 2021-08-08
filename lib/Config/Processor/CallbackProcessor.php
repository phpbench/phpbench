<?php

namespace PhpBench\Config\Processor;

use Closure;
use PhpBench\Config\ConfigLoader;
use PhpBench\Config\ConfigProcessor;

class CallbackProcessor implements ConfigProcessor
{
    /**
     * @var Closure
     */
    private $callback;

    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    public function process(ConfigLoader $loader, string $path, array $config): array
    {
        return $this->callback->call($this, $loader, $path, $config);
    }
}
