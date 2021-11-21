<?php

namespace PhpBench\Executor\Benchmark;

use PhpBench\Executor\BenchmarkExecutorInterface;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Executor\Parser\StageLexer;
use PhpBench\Executor\Parser\StageParser;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StageExecutor implements BenchmarkExecutorInterface
{
    /**
     * @var StageLexer
     */
    private $lexer;

    /**
     * @var StageParser
     */
    private $parser;

    public function __construct(StageLexer $lexer, StageParser $parser)
    {
        $this->lexer = $lexer;
        $this->parser = $parser;
    }
    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setRequired('program');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ExecutionContext $context, Config $config): ExecutionResults
    {
        $program = $this->parser->parse($this->lexer->lex($config['program']));
        $script = $this->scriptBuilder->build($program);
        dd($script);
    }
}
