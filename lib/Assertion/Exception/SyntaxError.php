<?php

namespace PhpBench\Assertion\Exception;

use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;

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
                '',
                sprintf(
                    'position: %s, type: "%s", value: "%s"',
                    $target->offset,
                    $target->type,
                    $target->value
                )
            ]
        ));
    }
}
