<?php

namespace PhpBench\Tests\Unit\Assertion\Exception;

use PhpBench\Assertion\Exception\SyntaxError;
use PhpBench\Assertion\ExpressionLexer;
use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;

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

position: 8, type: "name", value: "Barfoo"
EOT
        , $error->getMessage());
    }
}
