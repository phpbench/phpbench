<?php

namespace PhpBench\Config;

use PhpBench\Config\Exception\ConfigFileNotFound;
use PhpBench\Config\Linter\SeldLinter;
use PhpBench\Config\Processor\IncludeGlobProcessor;
use PhpBench\Config\Processor\IncludeProcessor;

class ConfigLoader
{
    /**
     * @param  ConfigProcessor[] $processors
     */
    public function __construct(
        private readonly ConfigLinter $linter,
        private readonly array $processors
    ) {
    }

    public static function create(): self
    {
        return new self(new SeldLinter(), [
            new IncludeProcessor(),
            new IncludeGlobProcessor()
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function load(string $path): array
    {
        if (!file_exists($path)) {
            throw new ConfigFileNotFound(sprintf(
                'Config file "%s" not found',
                $path
            ));
        }

        $configRaw = (string)file_get_contents($path);
        $this->linter->lint($path, $configRaw);

        $config = (array)json_decode($configRaw, true);

        foreach ($this->processors as $processor) {
            $config = $processor->process($this, $path, $config);
        }

        return $config;
    }
}
