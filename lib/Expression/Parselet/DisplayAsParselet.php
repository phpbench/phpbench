<?php

namespace PhpBench\Expression\Parselet;

use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\InfixParselet;
use PhpBench\Expression\Parser;
use PhpBench\Expression\Precedence;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class DisplayAsParselet implements InfixParselet
{
    public const T_VAL_PRECISION = 'precision';

    public function tokenType(): string
    {
        return Token::T_AS;
    }

    public function parse(Parser $parser, Node $left, Tokens $tokens): Node
    {
        $tokens->chomp();

        return new DisplayAsNode(
            $left,
            new UnitNode($this->resolveUnit($tokens, $parser)),
            $this->resolvePrecision($tokens, $parser)
        );
    }

    public function precedence(): int
    {
        return Precedence::AS;
    }

    private function resolveUnit(Tokens $tokens, Parser $parser): Node
    {
        if ($tokens->current()->type === Token::T_UNIT) {
            return new StringNode($tokens->chomp()->value);
        }

        return $parser->parseExpression($tokens, Precedence::AS);
    }

    private function resolvePrecision(Tokens $tokens, Parser $parser): ?Node
    {
        if ($tokens->current()->type !== Token::T_NAME) {
            return null;
        }

        if ($tokens->current()->value !== self::T_VAL_PRECISION) {
            return null;
        }

        $tokens->chomp();

        return $parser->parseExpression($tokens, Precedence::AS);
    }
}
