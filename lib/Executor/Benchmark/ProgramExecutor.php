<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Executor\Parser\StageLexer;
use PhpBench\Executor\Parser\StageParser;
use PhpBench\Executor\ScriptBuilder;
use PhpBench\Executor\ScriptExecutor;
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
     * @var ScriptExecutor
     */
    private $executor;

    /**
     * @var MainResultFactory
     */
    private $resultFactory;

    public function __construct(
        StageLexer $lexer,
        StageParser $parser,
        ScriptBuilder $builder,
        ScriptExecutor $executor,
        MainResultFactory $resultFactory
    )
    {
        $this->lexer = $lexer;
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
        $options->setDefault('program', 'call_before_methods;memory_sampler{hrtime_sampler{call_subject}};');
        $options->setRequired('program');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        $program = $this->parser->parse($this->lexer->lex($config['program']));
        $script = $this->builder->build($context, $program);
        $results = $this->executor->execute($script);

        return ExecutionResults::fromResults(...array_map(function (string $type, array $resultData) {
            return $this->resultFactory->create($type, $resultData);
        }, array_keys($results), array_values($results)));
    }
}
