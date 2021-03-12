<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;

class ExpressionLanguage
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
