<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Assertion\Ast\FunctionNode;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;
use PhpBench\Expression\Ast\ArgumentListNode;
use PhpBench\Expression\Ast\DelimitedListNode;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;
use PhpBench\Expression\PrefixParselet;

class FunctionParselet implements PrefixParselet
{
    public function tokenType(): string
    {
        return Token::T_FUNCTION;
    }

    public function parse(Parser $parser, Tokens $tokens): Node
    {
        $functionToken = $tokens->chomp();
        $arguments = $parser->parse($tokens);
        $tokens->chomp(Token::T_CLOSE_PAREN);

        $arguments = $this->resolveArguments($arguments);

        return new FunctionNode(rtrim($functionToken->value, '('), $arguments);
    }

    /**
     * @return array<Node>
     */
    private function resolveArguments(Node $arguments): array
    {
        if ($arguments instanceof ArgumentListNode) {
            return $arguments->expressions();
        }

        return [$arguments];
    }
}
