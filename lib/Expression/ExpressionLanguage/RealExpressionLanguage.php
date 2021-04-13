<?php

namespace PhpBench\Expression\ExpressionLanguage;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Lexer;
use PhpBench\Expression\Parser;

class RealExpressionLanguage implements ExpressionLanguage
{
    /**
     * @var Lexer
     */
    private $lexer;
    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Lexer $lexer, Parser $parser)
    {
        $this->lexer = $lexer;
        $this->parser = $parser;
    }

    public function parse(string $expression): Node
    {
        return $this->parser->parse($this->lexer->lex($expression));
    }
}
