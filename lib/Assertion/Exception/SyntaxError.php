<?php

namespace PhpBench\Assertion\Exception;

use PhpBench\Assertion\Token;
use PhpBench\Assertion\Tokens;

class SyntaxError extends ExpressionError
{
    /**
     * @param array{type: string, position: int, value: mixed} $token
     */
    public static function forToken(Tokens $tokens, Token $target, string $message): self
    {
        $lines = [''];
        $expression = [];
        $underline = '';
        $found = false;

        $until = 0;
        foreach ($tokens as $token) {
            if ($token === $target) {
                $underline = str_repeat('-', $until + 1) . str_repeat('^', $token->length());
                break;
            }
            $until = $token->end();
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
