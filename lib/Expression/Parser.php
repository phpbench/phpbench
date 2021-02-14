<?php

namespace PhpBench\Expression;

use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Exception\SyntaxError;
use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;
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
     * @param Parselets<PrefixParselet> $prefixParselets
     * @param Parselets<InfixParselet> $infixParselets
     */
    public function __construct(
        Parselets $prefixParselets,
        Parselets $infixParselets
    )
    {
        $this->prefixParselets = $prefixParselets;
        $this->infixParselets = $infixParselets;
        $this->listParselet = new ArgumentListParselet();
    }

    public function parse(Tokens $tokens, ?string $expectedType = null): Node
    {
        $node = $this->doParse($tokens);
        if ($expectedType && !$node instanceof $expectedType) {
            throw SyntaxError::forToken(
                $tokens,
                $tokens->current(),
                sprintf(
                    'Expected node of type "%s" got "%s"',
                    $expectedType,
                    get_class($node)
                )
            );
        }

        return $node;
    }

    public function doParse(Tokens $tokens): Node
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
