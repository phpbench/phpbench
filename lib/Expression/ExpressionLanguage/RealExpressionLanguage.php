<?php

namespace PhpBench\Expression\ExpressionLanguage;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Lexer;
use PhpBench\Expression\Parser;

class RealExpressionLanguage implements ExpressionLanguage
{
    public function __construct(private readonly Lexer $lexer, private readonly Parser $parser)
    {
    }

    public function parse(string $expression): Node
    {
        return $this->parser->parse($this->lexer->lex($expression));
    }
}
