<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Executor\Parser\StageLexer;
use PhpBench\Executor\Parser\StageParser;
use PhpBench\Executor\Parser\UnitParser;
use PhpBench\Executor\ScriptBuilder;
use PhpBench\Executor\ScriptExecutor;
use PhpBench\Executor\ScriptExecutorInterface;
use PhpBench\Model\MainResultFactory;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProgramExecutor implements BenchmarkExecutorInterface
{
    /**
     * @var StageLexer
     */
    private $lexer;

    /**
     * @var StageParser
     */
    private $parser;

    /**
     * @var ScriptBuilder
     */
    private $builder;

    /**
     * @var ScriptExecutorInterface
     */
    private $executor;

    /**
     * @var MainResultFactory
     */
    private $resultFactory;

    public function __construct(
        UnitParser $parser,
        ScriptBuilder $builder,
        ScriptExecutorInterface $executor,
        MainResultFactory $resultFactory
    ) {
        $this->parser = $parser;
        $this->builder = $builder;
        $this->executor = $executor;
        $this->resultFactory = $resultFactory;
    }
    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefault('program', [
            'before_methods',
            'hrtime_sampler',
            [
                'call_subject',
            ],
            'memory_sampler'
        ]);
        $options->setAllowedTypes('program', ['array']);
        $options->setRequired('program');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        $program = $this->parser->parse($config['program']);
        $script = $this->builder->build($context, $program);
        $results = $this->executor->execute($script);

        return ExecutionResults::fromResults(...array_map(function (string $type, array $resultData) {
            return $this->resultFactory->create($type, $resultData);
        }, array_keys($results), array_values($results)));
    }
}
