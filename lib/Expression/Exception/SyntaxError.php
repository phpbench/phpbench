<?php

namespace PhpBench\Expression\Exception;

use PhpBench\Expression\Token;
use PhpBench\Expression\Tokens;
use function str_pad;
use function str_repeat;
use function str_replace;
use function substr_replace;

class SyntaxError extends ExpressionError
{
    const TRUNCATE_AT = 20;

    public static function forToken(Tokens $tokens, Token $target, string $message, int $truncateAt = self::TRUNCATE_AT): self
    {
        $lines = [''];
        $expression = [];
        $underline = '';
        $found = false;

        $expr = $tokens->toString();
        $center = (int)$target->start() + ($target->length() / 2);

        foreach ($tokens as $token) {
            if ($token === $target) {
                $underline = str_repeat('-', $token->start()) . str_repeat('^', $token->length());

                break;
            }
        }
        $truncate = function (string $expr, int $center, string $elipsis = '…') {

            $tStart = max(0, $center - self::TRUNCATE_AT);
            $tEnd = $center + self::TRUNCATE_AT;

            $truncated = mb_substr($expr, $tStart, $tEnd - $tStart);

            if ($tEnd < mb_strlen($expr)) {
                $truncated = $truncated . ' ' . $elipsis;
            }

            if ($tStart > 0) {
                $truncated = $elipsis . ' ' . $truncated;
            }

            return $truncated;
        };

        return new self(implode(
            "\n",
            [
                '',
                $message . ':',
                '',
                '    ' . $truncate($expr, $center),
                '    ' . $truncate($underline, $center, ' '),
            ]
        ));
    }
}
