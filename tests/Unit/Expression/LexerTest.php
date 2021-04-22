<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Lexer;
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

    public function testIgnoresNewLinesWithWindowsLineBreak(): void
    {
        $tokens = $this->lex("\r\n10\r\n<\r\n20");
        self::assertCount(3, $tokens);
        self::assertEquals(10, $tokens->chomp()->value);
        self::assertEquals('<', $tokens->chomp()->value);
        self::assertEquals('20', $tokens->chomp()->value);
    }

    private function lex(string $string): Tokens
    {
        return (new Lexer([], []))->lex($string);
    }
}
