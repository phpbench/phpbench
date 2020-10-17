<?php

namespace PhpBench\Benchmark\Remote;

final class PayloadConfigBuilder
{
    /**
     * @var string
     */
    private $templatePath;

    /**
     * @var array<string,mixed>
     */
    private $tokens;

    /**
     * @var float
     */
    private $timeout;

    /**
     * @var array<string,mixed> $phpConfig
     */
    private $phpConfig = [];

    /**
     * @param array<string,mixed> $tokens
     */
    public function __construct(
        string $templatePath,
        array $tokens
    ) {
        $this->templatePath = $templatePath;
        $this->tokens = $tokens;
    }

    public function withTimeout(?float $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function includePhpConfig(array $phpConfig): self
    {
        $this->phpConfig = $phpConfig;

        return $this;
    }

    public function build(): PayloadConfig
    {
        return new PayloadConfig(
            $this->templatePath,
            $this->tokens,
            $this->timeout,
            $this->phpConfig
        );
    }
}
