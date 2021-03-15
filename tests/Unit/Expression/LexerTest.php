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

    private function lex(string $string): Tokens
    {
        return (new Lexer([], []))->lex($string);
    }
}
