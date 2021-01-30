<?php

namespace PhpBench\Assertion\Exception;

use PhpBench\Assertion\Token;

class SyntaxError extends ExpressionError
{
    /**
     * @param array{type: string, position: int, value: mixed} $token
     */
    public static function forToken(string $expression, string $message, Token $token): self
    {
        $out = [''];
        $out[] = $expression;
        $out[] = str_repeat('-', $token->offset) . '^';
        $error = sprintf(
            '%s (type: "%s", position: %s, value: %s)',
            $message,
            $token->type,
            $token->offset,
            json_encode($token->value)
        );

        $out[] = $error;

        return new self(implode("\n", $out));
    }
}
