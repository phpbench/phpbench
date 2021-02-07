<?php

namespace PhpBench\Console\Command;

use PhpBench\Assertion\ExpressionEvaluatorFactory;
use PhpBench\Assertion\ExpressionLexer;
use PhpBench\Assertion\ExpressionParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EvaluateCommand extends Command
{
    /**
     * @var ExpressionEvaluatorFactory
     */
    private $factory;
    /**
     * @var ExpressionLexer
     */
    private $lexer;
    /**
     * @var ExpressionParser
     */
    private $parser;

    public function __construct(
        ExpressionEvaluatorFactory $factory,
        ExpressionLexer $lexer,
        ExpressionParser $parser
    ) {
        parent::__construct();
        $this->factory = $factory;
        $this->lexer = $lexer;
        $this->parser = $parser;
    }

    public function configure(): void
    {
        $this->setName('eval');
        $this->addArgument('expr', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $expr = $input->getArgument('expr');
        assert(is_string($expr));

        $node = $this->parser->parse(
            $this->lexer->lex($expr)
        );
        $output->writeln((string)json_encode(
            $this->factory->createWithParameters([])->evaluate(
                $node
            )
        ));

        return 0;
    }
}
