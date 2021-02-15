<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Exception\SyntaxError;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;
use PhpBench\Expression\Ast\DelimitedListNode;
use PhpBench\Expression\Parselet\ArgumentListParselet;
use PhpBench\Expression\Parselets;

class Parser
{
    /**
     * @var Parselets<PrefixParselet>
     */
    private $prefixParselets;

    /**
     * @var Parselets<InfixParselet>
     */
    private $infixParselets;

    /**
     * @var Tokens
     */
    private $tokens;

    /**
     * @var ArgumentListParselet
     */
    private $listParselet;

    /**
     * @var Parselets
     */
    private $suffixParselets;

    /**
     * @param Parselets<PrefixParselet> $prefixParselets
     * @param Parselets<InfixParselet> $infixParselets
     */
    public function __construct(
        Parselets $prefixParselets,
        Parselets $infixParselets,
        Parselets $suffixParselets
    )
    {
        $this->prefixParselets = $prefixParselets;
        $this->infixParselets = $infixParselets;
        $this->listParselet = new ArgumentListParselet();
        $this->suffixParselets = $suffixParselets;
    }

    public function parse(Tokens $tokens): Node
    {
        $expression = $this->parseExpression($tokens);

        if ($tokens->current()->type === Token::T_COMMA) {
            return $this->listParselet->parse($this, $expression, $tokens);
        }

        return $expression;
    }

    public function parseExpression(Tokens $tokens, int $precedence = 0): Node
    {
        $token = $tokens->current();
        $left = $this->prefixParselets->forToken($token)->parse($this, $tokens);

        if (Token::T_EOF === $tokens->current()->type) {
            return $left;
        }

        $suffixParser = $this->suffixParselets->forTokenOrNull($tokens->current());
        if ($suffixParser instanceof SuffixParselet) {
            $left = $suffixParser->parse($left, $tokens);
        }

        while ($precedence < $this->infixPrecedence($tokens->current())) {
            $infixParselet = $this->infixParselets->forToken($tokens->current());
            $left = $infixParselet->parse($this, $left, $tokens);
        }


        return $left;
    }

    private function infixPrecedence(Token $token): int
    {
        $infixParser = $this->infixParselets->forTokenOrNull($token);

        if (!$infixParser) {
            return 0;
        }

        return $infixParser->precedence();
    }
}
