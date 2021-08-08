<?php

namespace PhpBench\Config;

interface ConfigProcessor
{
    public function process(ConfigLoader $loader, string $path, array $config): array;
}
