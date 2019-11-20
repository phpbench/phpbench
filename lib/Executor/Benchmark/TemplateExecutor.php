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

namespace PhpBench\Executor\Benchmark;

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Model\Iteration;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateExecutor implements BenchmarkExecutorInterface
{
    public const OPTION_PHP_CONFIG = 'php_config';
    private const PHP_OPTION_MAX_EXECUTION_TIME = 'max_execution_time';

    /**
     * @var Launcher
     */
    private $launcher;

    /**
     * @var string
     */
    private $templatePath;

    public function __construct(Launcher $launcher, string $templatePath)
    {
        $this->launcher = $launcher;
        $this->templatePath = $templatePath;
    }

    public function execute(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config): void
    {
        $tokens = $this->createTokens($subjectMetadata, $iteration, $config);

        $payload = $this->launcher->payload($this->templatePath, $tokens, $subjectMetadata->getTimeout());
        $this->launch($payload, $iteration, $config);
    }

    private function launch(Payload $payload, Iteration $iteration, Config $options)
    {
        $payload->mergePhpConfig(array_merge(
            [
                self::PHP_OPTION_MAX_EXECUTION_TIME => 0,
            ],
            $options[self::OPTION_PHP_CONFIG] ?? []
        ));

        $result = $payload->launch();

        if (isset($result['buffer']) && $result['buffer']) {
            throw new \RuntimeException(sprintf(
                'Benchmark made some noise: %s',
                $result['buffer']
            ));
        }

        $iteration->setResult(new TimeResult($result['time']));
        $iteration->setResult(MemoryResult::fromArray($result['mem']));
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options)
    {
        $options->setDefaults([
            self::OPTION_PHP_CONFIG => [
            ]
        ]);
    }

    /**
     * @param SubjectMetadata $subjectMetadata
     * @param Iteration $iteration
     *
     * @return array
     */
    protected function createTokens(SubjectMetadata $subjectMetadata, Iteration $iteration, Config $config) : array
    {
        $parameterSet = $iteration->getVariant()->getParameterSet();

        return [
            'class' => $subjectMetadata->getBenchmark()->getClass(),
            'file' => $subjectMetadata->getBenchmark()->getPath(),
            'subject' => $subjectMetadata->getName(),
            'revolutions' => $iteration->getVariant()->getRevolutions(),
            'beforeMethods' => var_export($subjectMetadata->getBeforeMethods(), true),
            'afterMethods' => var_export($subjectMetadata->getAfterMethods(), true),
            'parameters' => $parameterSet->count() ? var_export($parameterSet->getArrayCopy(), true) : '',
            'warmup' => $iteration->getVariant()->getWarmup() ?: 0,
        ];
    }
}
