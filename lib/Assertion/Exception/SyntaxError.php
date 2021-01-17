<?php

namespace PhpBench\Assertion\Exception;

use RuntimeException;

class SyntaxError extends ExpressionError
{
    /**
     * @param array{type: string, position: int, value: mixed} $token
     */
    public static function fromToken(string $expression, string $message, array $token): self
    {
        $out = [''];
        $out[] = $expression;
        $out[] = str_repeat('-', $token['position']) . '^';
        $error = sprintf(
            '%s (type: "%s", position: %s, value: %s)',
            $message,
            $token['type'],
            $token['position'],
            json_encode($token['value'])
        );

        $out[] = $error;
        return new self(implode("\n", $out));
    }
}
