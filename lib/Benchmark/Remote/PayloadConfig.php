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
     * @var string
     */
    private $phpBinary;

    /**
     * @var string|null
     */
    private $renderPath;

    /**
     * @param array<string,mixed> $tokens
     * @param array<string,mixed> $phpConfig
     */
    public function __construct(
        string $templatePath,
        array $tokens = [],
        ?float $timeout = null,
        array $phpConfig = [],
        ?string $renderPath = null,
        ?string $phpBinary = null
    )
    {
        $this->templatePath = $templatePath;
        $this->tokens = $tokens;
        $this->timeout = $timeout;
        $this->phpConfig = $phpConfig;
        $this->renderPath = $renderPath;
        $this->phpBinary = $phpBinary;
    }

    /**
     * @param array<string, mixed> $tokens
     */
    public static function builder(string $path, array $tokens): PayloadConfigBuilder
    {
        return new PayloadConfigBuilder($path, $tokens);
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

    public function getRenderPath(): ?string
    {
        return $this->renderPath;
    }

    public function getPhpBinary(): string
    {
        return $this->phpBinary;
    }
}
