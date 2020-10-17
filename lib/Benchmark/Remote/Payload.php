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

namespace PhpBench\Benchmark\Remote;

use PhpBench\Benchmark\Remote\Exception\ScriptErrorException;
use PhpBench\Executor\Exception\ExecutorScriptError;
use RuntimeException;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;
use Throwable;

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
    private $phpWrapper;

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
     * @var string|null
     */
    private $renderPath;

    /**
     * @var bool
     */
    private $disableIni;

    /**
     * @var callable|null
     */
    private $validator;

    /**
     * @var bool
     */
    private $removeScript;

    /**
     * Create a new Payload object with the given script template.
     * The template must be the path to a script template.
     */
    public function __construct(
        string $template,
        array $tokens = [],
        string $phpPath = PHP_BINARY,
        bool $disableIni = false,
        ?string $phpWrapper = null,
        ?float $timeout = null,
        ?array $phpConfig = [],
        ?string $renderPath = null,
        bool $removeScript = true,
        ?ProcessFactory $processFactory = null,
        ?callable $validator = null
    ) {
        $this->template = $template;
        $this->tokens = $tokens;
        $this->processFactory = $processFactory ?: new ProcessFactory();
        $this->iniStringBuilder = new IniStringBuilder();
        $this->timeout = $timeout;
        $this->phpPath = escapeshellarg($phpPath);
        $this->phpConfig = $phpConfig;
        $this->renderPath = $renderPath;
        $this->disableIni = $disableIni;
        $this->phpWrapper = $phpWrapper;
        $this->validator = $validator;
        $this->removeScript = $removeScript;
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

        if (!is_file($this->template)) {
            throw new \RuntimeException(sprintf(
                'Template path "%s" points to a non-file',
                $this->template
            ));
        }

        return file_get_contents($this->template);
    }

    private function writeTempFile(string $script): string
    {
        $scriptPath = $this->renderPath() ?: tempnam(sys_get_temp_dir(), 'PhpBench');
        file_put_contents($scriptPath, $script);

        return $scriptPath;
    }

    private function buildCommandLine(string $scriptPath): string
    {
        $arguments = [];

        if ($this->phpWrapper) {
            $arguments[] = $this->phpWrapper;
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
        if (!$this->removeScript) {
            return;
        }

        unlink($scriptPath);
    }

    private function decodeResults(Process $process): array
    {
        $output = $process->getOutput();
        $result = @unserialize($output);

        if (!is_array($result)) {
            throw new \RuntimeException(sprintf(
                'Script "%s" did not return an array, got: %s',
                $this->template,
                $output
            ));
        }

        if (!$this->validator) {
            return $result;
        }

        $resolver = new OptionsResolver();
        $validator = $this->validator;
        $validator($resolver);

        try {
            return $resolver->resolve($result);
        } catch (ExceptionInterface $error) {
            throw new ExecutorScriptError($error->getMessage());
        } catch (Throwable $error) {
            throw $error;
        }
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getPhpWrapper(): ?string
    {
        return $this->phpWrapper;
    }

    public function getPhpConfig(): array
    {
        return $this->phpConfig;
    }

    public function getPhpPath(): string
    {
        return $this->phpPath;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getTimeout(): float
    {
        return $this->timeout;
    }

    public function getRenderPath(): string
    {
        return $this->renderPath;
    }

    public function getDisableIni(): bool
    {
        return $this->disableIni;
    }

    private function renderPath(): ?string
    {
        if (!$this->renderPath) {
            return null;
        }

        if (!file_exists(dirname($this->renderPath))) {
            if (!@mkdir($this->renderPath, 0777, true)) {
                throw new RuntimeException(sprintf(
                    'Could not create directory for render path "%s"',
                    dirname($this->renderPath)
                ));
            }
        }

        if (file_exists($this->renderPath) && !is_file($this->renderPath)) {
            throw new RuntimeException(sprintf(
                'Render path "%s" is not a file',
                $this->renderPath
            ));
        }

        return $this->renderPath;
    }
}
