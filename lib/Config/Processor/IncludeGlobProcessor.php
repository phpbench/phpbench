<?php

namespace PhpBench\Config\Processor;

use PhpBench\Config\ConfigLoader;
use PhpBench\Config\ConfigProcessor;
use Webmozart\PathUtil\Path;

class IncludeGlobProcessor implements ConfigProcessor
{
    public const DIRECTIVE = '$include-glob';

    public function process(ConfigLoader $loader, string $path, array $config): array
    {
        if (!isset($config[self::DIRECTIVE])) {
            return $config;
        }

        foreach ((array)$config[self::DIRECTIVE] as $globPath) {
            $globPath = Path::makeAbsolute($globPath, dirname($path));

            foreach (glob($globPath) as $includePath) {
                $config = array_merge_recursive(
                    $config,
                    $loader->load($includePath)
                );
                unset($config[self::DIRECTIVE]);
            }
        }

        return $config;
    }
}
