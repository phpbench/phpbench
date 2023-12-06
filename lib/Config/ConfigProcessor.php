<?php

namespace PhpBench\Config;

interface ConfigProcessor
{
    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function process(ConfigLoader $loader, string $path, array $config): array;
}
