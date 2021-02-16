<?php

namespace PhpBench\Expression\Exception;

use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;

class SyntaxError extends ExpressionError
{
    public static function forToken(Tokens $tokens, Token $target, string $message): self
    {
        $lines = [''];
        $expression = [];
        $underline = '';
        $found = false;

        foreach ($tokens as $token) {
            if ($token === $target) {
                $underline = str_repeat('-', $token->start()) . str_repeat('^', $token->length());

                break;
            }
        }

        return new self(implode(
            "\n",
            [
                '',
                $message . ':',
                '',
                '    ' . $tokens->toString(),
                '    ' . $underline,
            ]
        ));
    }
}
