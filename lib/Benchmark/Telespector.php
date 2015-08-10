<?php

namespace PhpBench\Benchmark;

use Symfony\Component\Process\Process;

/**
 * Build and execute parameterized scripts in separate processes.
 * The scripts should return a JSON encoded string.
 */
class Telespector
{
    /**
     * @var string
     */
    private $bootstrap;

    /**
     * @param mixed string
     */
    public function __construct($bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public function execute($template, array $parameters)
    {
        if (!file_exists($template)) {
            throw new \RuntimeException(sprintf(
                'Could not find script template "%s"',
                $template
            ));
        }

        $parameters['bootstrap'] = $this->bootstrap;

        $tokens = array();
        foreach ($parameters as $key => $value) {
            $tokens['{{ ' . $key . ' }}'] = $value;
        }

        $template = file_get_contents($template);
        $script = str_replace(
            array_keys($tokens),
            array_values($tokens),
            $template
        );

        $scriptPath = tempnam(sys_get_temp_dir(), 'PhpBench');
        file_put_contents($scriptPath, $script);

        $process = new Process(PHP_BINARY . ' ' . $scriptPath);
        $process->run();
        unlink($scriptPath);

        if (false === $process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'Could not execute script: %s %s %s',
                $process->getErrorOutput(),
                $process->getOutput(),
                $script
            ));
        }

        $output = $process->getOutput();
        $result = json_decode($output, true);

        if (null === $result) {
            throw new \RuntimeException(sprintf(
                'Could not decode return value from script (should be a JSON encoded string): %s',
                $output
            ));
        }

        return $result;
    }
}
