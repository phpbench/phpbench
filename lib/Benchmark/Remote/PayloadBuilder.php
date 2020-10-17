<?php

namespace PhpBench\Benchmark\Remote;

use PhpBench\Registry\Config;

final class PayloadBuilder
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
     * @var array<int|string,mixed> $phpConfig
     */
    private $phpConfig = [];

    /**
     * @var ?string
     */
    private $renderPath;

    /**
     * @var string
     */
    private $phpBinary;

    /**
     * @var ProcessFactory|null
     */
    private $processFactory;

    /**
     * @var bool
     */
    private $disableIni;

    /**
     * @var string
     */
    private $phpWrapper;

    /**
     * @var string
     */
    private $bootstrap;

    /**
     * @param array<string,mixed> $tokens
     */
    public function __construct(
        string $templatePath,
        array $tokens,
        ?ProcessFactory $processFactory = null
    ) {
        $this->templatePath = $templatePath;
        $this->tokens = $tokens;
        $this->processFactory = $processFactory ?: new ProcessFactory();
        $this->phpBinary = PHP_BINARY;
        $this->disableIni = false;
    }

    public function disableIni(): self
    {
        $this->disableIni = true;

        return $this;
    }

    public function withBootstrap(?string $bootstrap): self
    {
        $this->bootstrap = $bootstrap;

        return $this;
    }

    public function withPhpWrapper(?string $phpWrapper): self
    {
        $this->phpWrapper = $phpWrapper;

        return $this;
    }

    public function withTimeout(?float $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function withPhpBinary(?string $phpBinary): self
    {
        $this->phpBinary = $phpBinary;

        return $this;
    }

    public function includePhpConfig(array $phpConfig): self
    {
        $this->phpConfig = array_merge($this->phpConfig, $phpConfig);

        return $this;
    }

    public function withRenderPath(?string $renderPath): self
    {
        $this->renderPath = $renderPath;

        return $this;
    }

    public function build(): Payload
    {
        return new Payload(
            $this->templatePath,
            $this->tokens,
            $this->phpBinary,
            $this->disableIni,
            $this->phpWrapper,
            $this->timeout,
            $this->phpConfig,
            $this->renderPath,
            $this->processFactory
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function launch(): array
    {
        return $this->build()->launch();
    }
}
