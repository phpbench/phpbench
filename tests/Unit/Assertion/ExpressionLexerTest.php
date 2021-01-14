<?php

namespace PhpBench\Tests\Unit\Assertion;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\ExpressionLexer;

class ExpressionLexerTest extends TestCase
{
    /**
     * @dataProvider provideLex
     */
    public function testLex(string $input, array $expected)
    {
        $lexer = new ExpressionLexer([]);
        $lexer->setInput($input);
        $tokens = $lexer->getInputUntilPosition(strlen($input));
        dump($tokens);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideLex(): Generator
    {
        yield [
            '123',
            [
                ExpressionLexer::T_INTEGER
            ]
        ];
    }
    
}
