<?php

namespace PhpBench\Benchmark;

use Symfony\Component\Process\Process;

class Executor
{
    /**
     * @param string $bootstrap
     * @param string $class
     * @param string $method
     * @param integer $revolutions
     * @param string[] $beforeMethods
     */
    public function execute($bootstrap, $class, $subject, $revolutions = 0, $beforeMethods = array())
    {
        $template = file_get_contents(__DIR__ . '/template/runner.template');

        $tokens = array(
            '{{ bootstrap }}' => $bootstrap,
            '{{ class }}' => $class,
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
}
