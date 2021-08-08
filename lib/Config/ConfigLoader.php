<?php

namespace PhpBench\Config;

use PhpBench\Config\Exception\ConfigFileNotFound;
use PhpBench\Config\Linter\SeldLinter;
use PhpBench\Config\Processor\IncludeGlobProcessor;
use PhpBench\Config\Processor\IncludeProcessor;

class ConfigLoader
{
    /**
     * @var ConfigProcessor[]
     */
    private $processors;

    /**
     * @var ConfigLinter
     */
    private $linter;

    public function __construct(ConfigLinter $linter, array $processors)
    {
        $this->processors = $processors;
        $this->linter = $linter;
    }

    public static function create(): self
    {
        return new self(new SeldLinter(), [
            new IncludeProcessor(),
            new IncludeGlobProcessor()
        ]);
    }

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
