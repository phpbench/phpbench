<?php

namespace PhpBench\Expression;

use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Exception\ParseletNotFound;
use PhpBench\Expression\Exception\SyntaxError;

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
    ) {
        $this->prefixParselets = $prefixParselets;
        $this->infixParselets = $infixParselets;
        $this->suffixParselets = $suffixParselets;
    }

    public function parse(Tokens $tokens): Node
    {
        $tokens = $tokens->withoutWhitespace();
        $node = $this->parseList($tokens);

        if ($tokens->hasMore()) {
            throw SyntaxError::forToken(
                $tokens,
                $tokens->current(),
                sprintf('Unexpected "%s" at end of expression', $tokens->current()->type)
            );
        }

        return $node;
    }

    public function parseList(Tokens $tokens): Node
    {
        $expression = $this->parseExpression($tokens);

        $list = [$expression];

        while ($tokens->current()->type === Token::T_COMMA) {
            $tokens->chomp();
            $list[] = $this->parseExpression($tokens);
        }

        if (count($list) > 1) {
            return new ArgumentListNode($list);
        }

        return $expression;
    }

    public function parseExpression(Tokens $tokens, int $precedence = 0): Node
    {
        $token = $tokens->current();

        try {
            $left = $this->prefixParselets->forToken($token)->parse($this, $tokens);
        } catch (ParseletNotFound $notFound) {
            throw SyntaxError::forToken($tokens, $token, sprintf(
                'Could not find parselet for "%s" token',
                $token->type
            ));
        }

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
