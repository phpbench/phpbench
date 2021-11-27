<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Executor\Parser\StageLexer;
use PhpBench\Executor\Parser\StageParser;
use PhpBench\Executor\ScriptBuilder;
use PhpBench\Executor\ScriptExecutor;
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

    public function __construct(
        StageLexer $lexer,
        StageParser $parser,
        ScriptBuilder $builder,
        ScriptExecutor $executor
    )
    {
        $this->lexer = $lexer;
        $this->parser = $parser;
        $this->builder = $builder;
        $this->executor = $executor;
    }
    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefault('program', 'hrtime_sampler{call_before_methods;call_subject}');
        $options->setRequired('program');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        $program = $this->parser->parse($this->lexer->lex($config['program']));
        $script = $this->builder->build($context, $program);
        dd($this->executor->execute($script));

        return ExecutionResults::fromResults();
    }
}
