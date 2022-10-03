<?php

namespace PhpBench\Expression\Exception;

use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;
use PhpBench\Util\TextTruncate;

use function str_repeat;

class SyntaxError extends ExpressionError
{
    public static function forToken(Tokens $tokens, Token $target, string $message, int $length = null): self
    {
        $lines = [''];
        $expression = [];
        $underline = '';
        $found = false;

        $expr = $tokens->toString();
        $center = (int)($target->start() + ($target->length() / 2));

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
                '    ' . TextTruncate::centered($expr, $center, '…', $length),
                '    ' . TextTruncate::centered($underline, $center, ' ', $length),
            ]
        ));
    }
}
