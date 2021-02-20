<?php

namespace PhpBench\Console\Command;

use PhpBench\Assertion\ExpressionEvaluatorFactory;
use PhpBench\Expression\Lexer;
use PhpBench\Assertion\ExpressionParser;
use PhpBench\Expression\MainEvaluator;
use PhpBench\Expression\Parser;
use PhpBench\Expression\ParserFactory;
use PhpBench\Expression\Printer;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function json_last_error_msg;

class EvaluateCommand extends Command
{
    const ARG_EXPR = 'expr';
    const ARG_PARAMS = 'params';


    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var MainEvaluator
     */
    private $evaluator;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Printer
     */
    private $printer;

    public function __construct(
        MainEvaluator $evaluator,
        Lexer $lexer,
        Parser $parser,
        Printer $printer
    ) {
        parent::__construct();
        $this->lexer = $lexer;
        $this->parser = $parser;
        $this->evaluator = $evaluator;
        $this->printer = $printer;
    }

    public function configure(): void
    {
        $this->setName('eval');
        $this->setDescription('Evaluate an expression with the PHPBench expression language');
        $this->addArgument(self::ARG_EXPR, InputArgument::REQUIRED);
        $this->addArgument(self::ARG_PARAMS, InputArgument::OPTIONAL, 'JSON encoded parameters');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $expr = $input->getArgument(self::ARG_EXPR);
        assert(is_string($expr));
        $params = $input->getArgument(self::ARG_PARAMS);
        assert(is_string($params) || is_null($params));

        $node = $this->parser->parse($this->lexer->lex($expr));
        $output->writeln(sprintf(
            '%s = %s',
            $this->printer->print($node, $params ?? []),
            $this->printer->print($this->evaluator->evaluate($node), $params ?: [])
        ));

        return 0;
    }

    /**
     * @return array<string,mixed>
     */
    private function resolveParams(?string $params): array
    {
        if (null === $params) {
            return [];
        }

        if (null === $params = json_decode($params, true)) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON: %s',
                json_last_error_msg()
            ));
        }
        return $params;
    }
}
