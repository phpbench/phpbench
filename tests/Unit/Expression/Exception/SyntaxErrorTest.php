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

        $error = SyntaxError::forToken($tokens, $token, 'invalid', [
            'position' => 5,
            'type' => Token::T_NONE,
            'value' => 'is',
        ]);
        self::assertEquals(<<<'EOT'

invalid:

    Foobar  Barfoo   Baz
    --------^^^^^^
EOT
        , $error->getMessage());
    }
}
