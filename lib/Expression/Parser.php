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

        if (!$next = $this->tokens->next()) {
            return $left;
        }

        do {
            $token = $this->tokens->chomp();
            $infixParselet = $this->infixParselets->forToken($token);
            $left = $infixParselet->parse($this, $left, $token);
        } while ($token = $this->tokens->hasAnother());

        return $left;
    }
}
