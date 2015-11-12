<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark\Executor;

use PhpBench\Benchmark\ExecutorInterface;
use PhpBench\Benchmark\Iteration;
use PhpBench\Benchmark\IterationResult;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Metadata\SubjectMetadata;

/**
 * This class generates a benchmarking script and places it in the systems
 * temp. directory and then executes it. The generated script then returns the
 * time taken to execute the benchmark and the memory consumed.
 */
class MicrotimeExecutor implements ExecutorInterface
{
    /**
     * @var Launcher
     */
    private $launcher;

    /**
     * @param Launcher $launcher
     * @param string $configPath
     * @param string $bootstrap
     */
    public function __construct(Launcher $launcher)
    {
        $this->launcher = $launcher;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Iteration $iteration, array $options = array())
    {
        $subject = $iteration->getSubject();
        $parameters = $this->resolveParameterOrder(
            $subject,
            $iteration->getParameters()
        );

        $tokens = array(
            'class' => $subject->getBenchmarkMetadata()->getClass(),
            'file' => $subject->getBenchmarkMetadata()->getPath(),
            'subject' => $subject->getName(),
            'revolutions' => $iteration->getRevolutions(),
            'beforeMethods' => var_export($subject->getBeforeMethods(), true),
            'afterMethods' => var_export($subject->getAfterMethods(), true),
            'parameters' => var_export($parameters, true),
            'argString' => $this->getArgString(count($parameters))
        );

        $payload = $this->launcher->payload(__DIR__ . '/template/microtime.template', $tokens);
        $result = $payload->launch();

        return new IterationResult($result['time'], $result['memory']);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfig()
    {
        return array();
    }

    private function resolveParameterOrder(SubjectMetadata $subject, array $parameters)
    {
        $isAssoc = false;
        foreach (array_keys($parameters) as $key) {
            if (is_string($key)) {
                $isAssoc = true;
                continue;
            }

            if (true === $isAssoc && is_numeric($key)) {
                throw new \RuntimeException(sprintf(
                    'Cannot mix numeric and string indexes in parameter set. Got: "%s"',
                    implode('", "', array_keys($parameters))
                ));
            }
        }

        if ($isAssoc) {
            $parameters = $this->mapAssocToArgs($subject, $parameters);
        }

        return $parameters;
    }

    private function mapAssocToArgs(SubjectMetadata $subject, array $parameters)
    {
        $args = $subject->getArguments();

        if (count($args) && array_diff(array_keys($parameters), $args)) {
            throw new \RuntimeException(sprintf(
                'You have used an associative array for parameters. The argument names on the benchmarking ' . 
                'subject "%s" must match the top level key names of the provided parameter set. Should be: "%s", current ' . 
                'argument names: "%s"',
                $subject->getName(),
                implode('", "', array_keys($parameters)),
                implode('", "', $args)
            ));
        }

        if (count($args) && $diff = array_diff($args, array_keys($parameters))) {
            throw new \RuntimeException(sprintf(
                'Subject "%s" expects parameters: "%s", but did not get them.',
                $subject->getName(),
                implode('", "', $diff)
            ));
        }

        $resolved = array();

        foreach ($parameters as $key => $value) {
            $argIndex = array_search($key, $args);

            if (false === $argIndex) {
                continue;
            }

            $resolved[$argIndex] = $value;
        }

        return $resolved;
    }

    private function getArgString($argCount)
    {
        $string = array();

        for ($i = 0; $i < $argCount; $i++) {
            $string[] = sprintf('$parameters[%s]', $i);
        }

        return implode(', ', $string);
    }
}
