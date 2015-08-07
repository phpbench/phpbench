<?php

namespace PhpBench\Benchmark;

use Symfony\Component\Process\Process;
use PhpBench\BenchmarkInterface;

class Executor
{
    /**
     * @var string
     */
    private $bootstrap;

    /**
     * @var string
     */
    private $configDir;

    /**
     * @param mixed $bootstrap
     */
    public function __construct($configDir, $bootstrap)
    {
        $this->configDir = $configDir;
        $this->bootstrap = $bootstrap;
    }

    /**
     * @param string $bootstrap
     * @param string $class
     * @param string $method
     * @param integer $revolutions
     * @param string[] $beforeMethods
     */
    public function execute(BenchmarkInterface $benchmark, $subject, $revolutions = 0, $beforeMethods = array())
    {
        $refl = new \ReflectionClass($benchmark);
        $template = file_get_contents(__DIR__ . '/template/runner.template');

        $tokens = array(
            '{{ bootstrap }}' => $this->getBootstrapPath(),
            '{{ class }}' => $refl->getName(),
            '{{ file }}' => $refl->getFileName(),
            '{{ subject }}' => $subject,
            '{{ revolutions }}' => $revolutions,
            '{{ beforeMethods }}' => var_export($beforeMethods, true),
        );

        $script = str_replace(
            array_keys($tokens),
            array_values($tokens),
            $template
        );

        $scriptPath = tempnam(sys_get_temp_dir(), 'PhpBench');
        file_put_contents($scriptPath, $script);

        $process = new Process('php ' . $scriptPath);
        $process->run();
        unlink($scriptPath);

        if (false === $process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'Could not execute benchmark subject: %s %s',
                $process->getErrorOutput(),
                $process->getOutput()
            ));
        }

        $result = json_decode($process->getOutput(), true);

        return $result;
    }

    private function getBootstrapPath()
    {
        // if the path is absolute, return it unmodified
        if ('/' === substr($this->bootstrap, 0, 1)) {
            return $this->bootstrap;
        }

        return $this->configDir . '/' . $this->bootstrap;
    }
}
