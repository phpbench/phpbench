<?php

namespace PhpBench\Tests\Unit\Expression\Exception;

use PhpBench\Expression\Exception\SyntaxError;
use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;
use PHPUnit\Framework\TestCase;

class SyntaxErrorTest extends TestCase
{
    public function testFromToken(): void
    {
        $token = new Token(Token::T_NAME, 'Barfoo', 8);

        $tokens = new Tokens([
            new Token(Token::T_NAME, 'Foobar', 0),
            $token,
            new Token(Token::T_NAME, 'Baz', 17),
        ]);

        $error = SyntaxError::forToken($tokens, $token, 'invalid');
        self::assertEquals(<<<'EOT'

invalid:

    Foobar  Barfoo   Baz
    --------^^^^^^
EOT
            , $error->getMessage());
    }

    public function testFromTokenTruncatesToMaxLength(): void
    {
        $token = new Token(Token::T_NAME, 'Barfoo', 24);

        $tokens = new Tokens([
            new Token(Token::T_NAME, 'FoobarFoobarFoobarFoobar', 0),
            $token,
            new Token(Token::T_NAME, 'BarfooBarfooBarBarfoo', 30),
        ]);

        $error = SyntaxError::forToken($tokens, $token, 'invalid', 20);
        self::assertEquals(<<<'EOT'

invalid:

    … oobarFoobarFoobarBarfooBarfooBarfooBarBa …
      -----------------^^^^^^
EOT
            , $error->getMessage());
    }
}
