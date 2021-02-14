<?php

namespace PhpBench\Expression;

use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;
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
     * @param Parselets<PrefixParselet> $prefixParselets
     * @param Parselets<InfixParselet> $infixParselets
     */
    public function __construct(
        Tokens $tokens,
        Parselets $prefixParselets,
        Parselets $infixParselets
    )
    {
        $this->prefixParselets = $prefixParselets;
        $this->infixParselets = $infixParselets;
        $this->tokens = $tokens;
    }

    public function parse(int $precedence = 0): Node
    {
        $token = $this->tokens->chomp();
        $left = $this->prefixParselets->forToken($token)->parse($token);

        if (Token::T_EOF === $this->tokens->current()->type) {
            return $left;
        }

        while ($precedence < $this->infixPrecedence()) {
            $token = $this->tokens->chomp();
            $infixParselet = $this->infixParselets->forToken($token);
            $left = $infixParselet->parse($this, $left, $token);
        }

        return $left;
    }

    private function infixPrecedence(): int
    {
        $infixParser = $this->infixParselets->forTokenOrNull($this->tokens->current());

        if (!$infixParser) {
            return 0;
        }

        return $infixParser->precedence();
    }
}
