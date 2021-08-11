<?php

namespace PhpBench\Tests\Unit\Expression;

use Generator;
use PhpBench\Expression\Lexer;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;
use PhpBench\Tests\Util\Approval;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    public function testIgnoresNewLines(): void
    {
        $tokens = $this->lex(
            <<<'EOT'
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

    /**
     * @dataProvider provideLex
     */
    public function testLex(string $path): void
    {
        $approval = Approval::create($path, 2);
        $string = $approval->getSection(0);
        $approval->approve(implode(', ', array_map(function (Token $token) {
            return sprintf('%s:%s[%s]', $token->start(), $token->value, $token->type);
        }, $this->lex($string)->toArray())));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideLex(): Generator
    {
        foreach (glob(__DIR__ . '/Lexer/*.test') as $path) {
            yield [$path];
        }
    }

    private function lex(string $string): Tokens
    {
        return (new Lexer([], []))->lex($string);
    }
}
