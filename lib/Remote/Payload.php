<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Remote;

use PhpBench\Remote\Exception\ScriptErrorException;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Class representing the context from which a script can be generated and executed by a PHP binary.
 */
class Payload
{
    const FLAG_DISABLE_INI = '-n';

    /**
     * Path to script template.
     */
    private $template;

    /**
     * Wrapper for PHP binary, e.g. "blackfire".
     */
    private $wrapper;

    /**
     * Associative array of PHP INI settings.
     */
    private $phpConfig = [];

    /**
     * Path to PHP binary.
     */
    private $phpPath;

    /**
     * Tokens to substitute in the script template.
     */
    private $tokens = [];

    /**
     * Symfony Process instance.
     */
    private $process;

    /**
     * @var bool
     */
    private $disableIni = false;

    /**
     * @var IniStringBuilder
     */
    private $iniStringBuilder;

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var float
     */
    private $timeout;

    /**
     * @var string
     */
    private $scriptPath;

    /**
     * @var bool
     */
    private $scriptRemove;

    /**
     * Create a new Payload object with the given script template.
     * The template must be the path to a script template.
     *
     * @param array<string, mixed> $tokens
     */
    public function __construct(
        string $template,
        array $tokens = [],
        ?string $phpPath = PHP_BINARY,
        ?float $timeout = null,
        ProcessFactory $processFactory = null,
        string $scriptPath = null,
        bool $scriptRemove = false
    ) {
        $this->setPhpPath($phpPath);
        $this->template = $template;
        $this->tokens = $tokens;
        $this->processFactory = $processFactory ?: new ProcessFactory();
        $this->iniStringBuilder = new IniStringBuilder();
        $this->timeout = $timeout;
        $this->phpPath = $phpPath;
        $this->scriptPath = $scriptPath;
        $this->scriptRemove = $scriptRemove;
    }

    public function setWrapper($wrapper): void
    {
        $this->wrapper = $wrapper;
    }

    public function mergePhpConfig(array $phpConfig): void
    {
        $this->phpConfig = array_merge(
            $this->phpConfig,
            $phpConfig
        );
    }

    public function setPhpPath($phpPath): void
    {
        $this->phpPath = escapeshellarg($phpPath);
    }

    public function disableIni(): void
    {
        $this->disableIni = true;
    }

    public function launch(): array
    {
        $script = $this->readFile();
        $script = $this->replaceTokens($script);
        $scriptPath = $this->writeTempFile($script);
        $commandLine = $this->buildCommandLine($scriptPath);

        $process = $this->processFactory->create($commandLine, $this->timeout);
        $process->run();

        $this->removeTmpFile($scriptPath);

        if (false === $process->isSuccessful()) {
            throw new ScriptErrorException(sprintf(
                '%s%s',
                $process->getErrorOutput(),
                $process->getOutput()
            ));
        }

        return $this->decodeResults($process);
    }

    private function getIniString(): string
    {
        if (empty($this->phpConfig)) {
            return '';
        }

        return $this->iniStringBuilder->build($this->phpConfig);
    }

    private function replaceTokens(string $templateBody): string
    {
        $tokenSubs = [];

        foreach ($this->tokens as $key => $value) {
            $tokenSubs['{{ ' . $key . ' }}'] = $value;
        }

        return str_replace(
            array_keys($tokenSubs),
            array_values($tokenSubs),
            $templateBody
        );
    }

    private function readFile(): string
    {
        if (!file_exists($this->template)) {
            throw new \RuntimeException(sprintf(
                'Could not find script template "%s"',
                $this->template
            ));
        }

        return file_get_contents($this->template);
    }

    private function writeTempFile(string $script): string
    {
        $scriptPath = $this->scriptPath ?
            $this->scriptPath . '/' . basename($this->template) :
            tempnam(sys_get_temp_dir(), 'PhpBench');

        if (false === $scriptPath) {
            throw new RuntimeException(
                'Could not generate temporary script name'
            );
        }

        (function (string $directory): void {
            if (file_exists($directory)) {
                return;
            }

            if (@mkdir($directory, 0744)) {
                return;
            }

            throw new RuntimeException(sprintf(
                'Could not create directory "%s"', $directory
            ));
        })(dirname($scriptPath));

        file_put_contents($scriptPath, $script);

        return $scriptPath;
    }

    private function buildCommandLine(string $scriptPath): string
    {
        $arguments = [];

        if ($this->wrapper) {
            $arguments[] = $this->wrapper;
        }

        $arguments[] = $this->phpPath;

        if (true === $this->disableIni) {
            $arguments[] = self::FLAG_DISABLE_INI;
        }

        $arguments[] = $this->getIniString();
        $arguments[] = escapeshellarg($scriptPath);

        return implode(' ', $arguments);
    }

    private function removeTmpFile(string $scriptPath): void
    {
        if (!$this->scriptRemove) {
            return;
        }

        unlink($scriptPath);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResults(Process $process): array
    {
        $output = $process->getOutput();
        $result = @unserialize($output);

        if (is_array($result)) {
            return $result;
        }

        throw new \RuntimeException(sprintf(
            'Script "%s" did not return an array, got: %s',
            $this->template,
            $output
        ));
    }
}
