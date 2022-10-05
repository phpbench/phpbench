<?php

namespace PhpBench\Executor;

use PhpBench\Remote\IniStringBuilder;
use Symfony\Component\Process\Process;

class PhpProcessFactory
{
    /**
     * @var IniStringBuilder
     */
    private $iniStringBuilder;

    /**
     * @var PhpProcessOptions
     */
    private $options;

    private const FLAG_DISABLE_INI = '-n';

    public function __construct(PhpProcessOptions $options)
    {
        $this->iniStringBuilder = new IniStringBuilder();
        $this->options = $options;
    }

    public function buildProcess(string $scriptPath): Process
    {
        $process = new Process($this->buildArgs($scriptPath));
        $process->setTimeout($this->options->timeout);

        return $process;
    }

    /**
     * @return string[]
     */
    private function buildArgs(string $scriptPath): array
    {
        $arguments = [];

        if ($this->options->phpWrapper) {
            $arguments[] = $this->options->phpWrapper;
        }

        $arguments[] = $this->options->phpPath;

        if (true === $this->options->disablePhpIni) {
            $arguments[] = self::FLAG_DISABLE_INI;
        }

        $arguments = array_merge($arguments, $this->getIniFlags());

        $arguments[] = $scriptPath;

        return $arguments;
    }

    /**
     * @return string[]
     */
    private function getIniFlags(): array
    {
        if (empty($this->options->phpConfig)) {
            return [];
        }

        return $this->iniStringBuilder->buildList($this->options->phpConfig);
    }
}
