<?php

namespace PhpBench\Expression;

use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;
use PhpBench\Expression\Ast\BinaryOperatorNode;
use PhpBench\Expression\Parselet\BinaryOperatorParselet;
use PhpBench\Expression\Parselet\FloatParselet;
use PhpBench\Expression\Parselet\FunctionParselet;
use PhpBench\Expression\Parselet\IntegerParselet;
use PhpBench\Expression\Parselet\ArgumentListParselet;
use PhpBench\Expression\Parselet\ListParselet;

final class ParserFactory
{
    public function create(): Parser
    {
        return new Parser(
            Parselets::fromPrefixParselets([
                new ListParselet(),
                new FunctionParselet(),
                new IntegerParselet(),
                new FloatParselet(),
            ]),
            Parselets::fromInfixParselets([
                new BinaryOperatorParselet(Token::T_PLUS, Precedence::SUM),
                new BinaryOperatorParselet(Token::T_MINUS, Precedence::SUM),
                new BinaryOperatorParselet(Token::T_MULTIPLY, Precedence::PRODUCT),
                new BinaryOperatorParselet(Token::T_DIVIDE, Precedence::PRODUCT),
                new BinaryOperatorParselet(Token::T_LT, Precedence::COMPARISON),
                new BinaryOperatorParselet(Token::T_LTE, Precedence::COMPARISON),
                new BinaryOperatorParselet(Token::T_EQUALS, Precedence::COMPARISON_EQUALITY),
                new BinaryOperatorParselet(Token::T_GT, Precedence::COMPARISON),
                new BinaryOperatorParselet(Token::T_GTE, Precedence::COMPARISON),
            ])
        );
    }
}
