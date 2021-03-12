<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Lexer;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    public function testIgnoresNewLines(): void
    {
        $tokens = $this->lex(<<<'EOT'
10
<
20
EOT
        );
        self::assertCount(3, $tokens);
    }

    public function testLexParameter(): void
    {
        $tokens = $this->lex(<<<'EOT'
foo.asd.asd
EOT
        );
        self::assertEquals('foo.asd.asd', $tokens->chomp(Token::T_PARAMETER)->value);

        $tokens = $this->lex(<<<'EOT'
foobar(foo.asd.asd)
EOT
        );
        $tokens->chomp(Token::T_FUNCTION);
        self::assertEquals('foo.asd.asd', $tokens->chomp(Token::T_PARAMETER)->value);
    }

    private function lex(string $string): Tokens
    {
        return (new Lexer([], []))->lex($string);
    }
}
