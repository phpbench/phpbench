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

use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\Exception\ExecutionError;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Registry\Config;
use PhpBench\Remote\Exception\ScriptErrorException;
use PhpBench\Remote\Launcher;
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

    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        $tokens = $this->createTokens($context);
        $payload = $this->launcher->payload($this->templatePath, $tokens, $context->getTimeout());
        $payload->mergePhpConfig(array_merge(
            [
                self::PHP_OPTION_MAX_EXECUTION_TIME => 0,
            ],
            $config[self::OPTION_PHP_CONFIG] ?? []
        ));

        try {
            $result = $payload->launch();
        } catch (ScriptErrorException $error) {
            throw new ExecutionError($error->getMessage(), 0, $error);
        }

        if (isset($result['buffer']) && $result['buffer']) {
            throw new \RuntimeException(sprintf(
                'Benchmark made some noise: %s',
                $result['buffer']
            ));
        }

        return ExecutionResults::fromResults(
            TimeResult::fromArray($result['time']),
            MemoryResult::fromArray($result['mem'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            self::OPTION_PHP_CONFIG => [
            ]
        ]);
    }

    protected function createTokens(ExecutionContext $context) : array
    {
        return [
            'class' => $context->getClassName(),
            'file' => $context->getClassPath(),
            'subject' => $context->getMethodName(),
            'revolutions' => $context->getRevolutions(),
            'beforeMethods' => var_export($context->getBeforeMethods(), true),
            'afterMethods' => var_export($context->getAfterMethods(), true),
            'parameters' => $context->getParameters() ? var_export($context->getParameters(), true) : '',
            'warmup' => $context->getWarmup() ?: 0,
        ];
    }
}
