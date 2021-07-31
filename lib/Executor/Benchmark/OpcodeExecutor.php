<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\Exception\ExecutionError;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\OpcodeResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Opcache\OpcodeDebugParser;
use PhpBench\Registry\Config;
use PhpBench\Remote\Exception\ScriptErrorException;
use PhpBench\Remote\Launcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpcodeExecutor implements BenchmarkExecutorInterface
{
    public const OPTION_PHP_CONFIG = 'php_config';
    public const OPTION_SAFE_PARAMETERS = 'safe_parameters';

    private const PHP_OPTION_MAX_EXECUTION_TIME = 'max_execution_time';

    /**
     * @var Launcher
     */
    private $launcher;

    /**
     * @var string
     */
    private $templatePath;

    /**
     * @var OpcodeDebugParser
     */
    private $parser;

    public function __construct(
        Launcher $launcher,
        OpcodeDebugParser $parser
    )
    {
        $this->launcher = $launcher;
        $this->parser = $parser;
    }

    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        $tokens = $this->createTokens($context, $config);
        $payload = $this->launcher->payload(
            __DIR__ . '/template/remote.template',
            $tokens,
            $context->getTimeout()
        );
        $payload->mergePhpConfig(array_merge(
            [
                self::PHP_OPTION_MAX_EXECUTION_TIME => 0,
            ],
            [
                'opcache.enable_cli' => 1,
                'opcache.opt_debug_level' => '0x10000',
            ],
            $config[self::OPTION_PHP_CONFIG] ?? [],
        ));


        try {
            $result = $payload->launchResult();
        } catch (ScriptErrorException $error) {
            throw new ExecutionError($error->getMessage(), 0, $error);
        }

        $data = $result->unserializeResult();
        file_put_contents('example', $result->stderr());

        return ExecutionResults::fromResults(
            new OpcodeResult($this->parser->countOpcodes($result->stderr())),
            TimeResult::fromArray($data['time']),
            MemoryResult::fromArray($data['mem'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            self::OPTION_PHP_CONFIG => [
            ],
            self::OPTION_SAFE_PARAMETERS => false,
        ]);
        $options->setAllowedTypes(self::OPTION_PHP_CONFIG, ['array']);
        $options->setAllowedTypes(self::OPTION_SAFE_PARAMETERS, ['bool']);
        SymfonyOptionsResolverCompat::setInfos($options, [
            self::OPTION_PHP_CONFIG => 'Key value array of ini settings, e.g. ``{"max_execution_time":100}``',
            self::OPTION_SAFE_PARAMETERS => 'INTERNAL: Use process process-safe parameters, this option exists for backwards-compatibility and will be removed in PHPBench 2.0'
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    protected function createTokens(ExecutionContext $context, Config $config) : array
    {
        return [
            'class' => $context->getClassName(),
            'file' => $context->getClassPath(),
            'subject' => $context->getMethodName(),
            'revolutions' => $context->getRevolutions(),
            'beforeMethods' => var_export($context->getBeforeMethods(), true),
            'afterMethods' => var_export($context->getAfterMethods(), true),
            'parameters' => var_export($this->resolveParameterSet($context, $config), true),
            'warmup' => $context->getWarmup() ?: 0,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function resolveParameterSet(ExecutionContext $context, Config $config): array
    {
        if (isset($config[self::OPTION_SAFE_PARAMETERS]) && $config[self::OPTION_SAFE_PARAMETERS]) {
            return $context->getParameterSet()->toSerializedParameters();
        }

        return $context->getParameterSet()->toUnserializedParameters();
    }
}
