<?php

namespace PhpBench\Tests\Unit\Expression;

use PHPUnit\Framework\TestCase;
use PhpBench\Expression\Lexer;

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

    private function lex(string $string)
    {
        return (new Lexer([], []))->lex($string);
    }
}
