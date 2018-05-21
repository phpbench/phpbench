<?php

namespace PhpBench\Model;

use PhpBench\Registry\Config;

class ResolvedExecutor
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Config
     */
    private $config;

    public function __construct(string $name, Config $config)
    {
        $this->name = $name;
        $this->config = $config;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public static function fromNameAndConfig(string $name, Config $executorConfig)
    {
        return new self($name, $executorConfig);
    }
}
