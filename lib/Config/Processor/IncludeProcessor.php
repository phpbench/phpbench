<?php

namespace PhpBench\Config\Processor;

use PhpBench\Config\ConfigLoader;
use PhpBench\Config\ConfigProcessor;
use PhpBench\Path\Path;

class IncludeProcessor implements ConfigProcessor
{
    public const DIRECTIVE = '$include';

    public function process(ConfigLoader $loader, string $path, array $config): array
    {
        if (!isset($config[self::DIRECTIVE])) {
            return $config;
        }

        foreach ((array)$config[self::DIRECTIVE] as $includePath) {
            $includePath = Path::makeAbsolute($includePath, dirname($path));
            $config = array_merge_recursive(
                $config,
                $loader->load($includePath)
            );
            unset($config[self::DIRECTIVE]);
        }

        return $config;
    }
}
