<?php

namespace PhpBench\Benchmark\Remote;

final class PayloadConfig
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
     * @var float|null
     */
    private $timeout;

    /**
     * @var array<string,mixed> $phpConfig
     */
    private $phpConfig;

    /**
     * @param array<string,mixed> $tokens
     * @param array<string,mixed> $phpConfig
     */
    public function __construct(
        string $templatePath,
        array $tokens = [],
        ?float $timeout = null,
        array $phpConfig = []
    )
    {
        $this->templatePath = $templatePath;
        $this->tokens = $tokens;
        $this->timeout = $timeout;
        $this->phpConfig = $phpConfig;
    }

    public function getTimeout(): ?float
    {
        return $this->timeout;
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * @return array<string,mixed>
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * @return array<string,mixed>
     */
    public function getPhpConfig(): array
    {
        return $this->phpConfig;
    }

    /**
     * @param array<string, mixed> $tokens
     */
    public static function builder(string $path, array $tokens): PayloadConfigBuilder
    {
        return new PayloadConfigBuilder($path, $tokens);
    }
}
