<?php

namespace PhpBench\Util;

use function mb_strlen;

class TextTruncate
{
    public const TRUNCATE_AT = 40;

    public static function centered(string $input, int $center, string $elipsis = '…', ?int $length = self::TRUNCATE_AT)
    {
        $length = $length ?: self::TRUNCATE_AT;
        $tStart = max(0, $center - $length);
        $tEnd = $center + $length;
        $inputLn = mb_strlen($input);

        if ($tStart > $inputLn) {
            return '';
        }

        $truncated = mb_substr($input, $tStart, $tEnd - $tStart);

        if ($tEnd < mb_strlen($input)) {
            $truncated = $truncated . ' ' . $elipsis;
        }

        if ($tStart > 0) {
            $truncated = $elipsis . ' ' . $truncated;
        }

        return $truncated;
    }
}
