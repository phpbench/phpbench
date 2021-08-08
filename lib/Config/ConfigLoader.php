<?php

namespace PhpBench\Config;

use PhpBench\Config\Linter\SeldLinter;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

class ConfigLoader
{
    /**
     * @var array
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
        return new self(new SeldLinter(), []);
    }

    public function load(string $path): array
    {
        $configRaw = (string)file_get_contents($path);
        $this->linter->lint($path, $configRaw);

        return (array)json_decode($configRaw, true);
    }
}
