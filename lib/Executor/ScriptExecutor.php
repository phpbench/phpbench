<?php

namespace PhpBench\Executor;

use PhpBench\Remote\Exception\ScriptErrorException;
use PhpBench\Remote\IniStringBuilder;
use PhpBench\Remote\ProcessFactory;
use PhpBench\Remote\ProcessFactoryInterface;
use RuntimeException;
use Symfony\Component\Process\Process;
use function uniqid;

final class ScriptExecutor
{
    /**
     * @var string|null
     */
    private $scriptPath;

    /**
     * @var bool
     */
    private $scriptRemove;

    /**
     * @var PhpProcessFactory
     */
    private $factory;

    public function __construct(
        PhpProcessFactory $factory,
        ?string $scriptPath,
        bool $scriptRemove
    ) {
        $this->scriptPath = $scriptPath;
        $this->factory = $factory;
        $this->scriptRemove = $scriptRemove;
    }

    /**
     * @return array<string,mixed>
     */
    public function execute(/** include the run ID here **/string $script): array
    {
        $scriptPath = $this->writeTempFile($script);
        $process = $this->factory->buildProcess($scriptPath);
        $process->run();
        $this->removeTmpFile($scriptPath);

        if (false === $process->isSuccessful()) {
            throw new ScriptErrorException(sprintf(
                '%s%s',
                $process->getErrorOutput(),
                $process->getOutput()
            ));
        }

        return $this->decodeResults($process, $scriptPath);
    }

    private function writeTempFile(string $script): string
    {
        $scriptPath = $this->scriptPath ?
            sprintf('%s/script_%s.php', $this->scriptPath, uniqid()) :
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

            if (@mkdir($directory, 0744, true)) {
                return;
            }

            throw new RuntimeException(sprintf(
                'Could not create directory "%s"',
                $directory
            ));
        })(dirname($scriptPath));

        file_put_contents($scriptPath, $script);

        return $scriptPath;
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
    private function decodeResults(Process $process, string $scriptPath): array
    {
        $output = $process->getOutput();

        $result = @unserialize($output);

        if (is_array($result)) {
            return $result;
        }

        throw new \RuntimeException(sprintf(
            'Script "%s" did not return an array, got: %s',
            $scriptPath,
            $output
        ));
    }
}
