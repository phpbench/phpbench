<?php

namespace PhpBench\Tests\Unit\Expression;

use PhpBench\Expression\Exception\SyntaxError;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;
use PHPUnit\Framework\TestCase;

class TokensTest extends TestCase
{
    public function testSyntaxErrorOnChomp(): void
    {
        $this->expectException(SyntaxError::class);
        $tokens = new Tokens([
            new Token(Token::T_NAME, 'foo', 0),
        ]);
        $tokens->chomp(Token::T_LOGICAL_AND);
    }
}
