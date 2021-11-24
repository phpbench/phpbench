<?php

namespace PhpBench\Executor;

class PhpProcessOptions
{
    /**
     * @var string
     */
    public $phpPath;
    /**
     * @var parameters
     */
    public $phpConfig;
    /**
     * @var bool
     */
    public $disablePhpIni;
    /**
     * @var string|null
     */
    public $phpWrapper;
    /**
     * @var int|null
     */
    public $timeout;

    /**
     * @param parameters $phpConfig
     */
    public function __construct(
        string $phpPath = PHP_BINARY,
        array $phpConfig = [],
        bool $disablePhpIni = false,
        ?string $phpWrapper = null,
        ?int $timeout = null,
    )
    {
        $this->phpPath = $phpPath;
        $this->phpConfig = $phpConfig;
        $this->disablePhpIni = $disablePhpIni;
        $this->phpWrapper = $phpWrapper;
        $this->timeout = $timeout;
    }
}
