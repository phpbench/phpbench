<?php

namespace PhpBench\Util;

class TextTruncate
{
    const TRUNCATE_AT = 20;

    public static function centered(string $expr, int $center, string $elipsis = '…', int $length = self::TRUNCATE_AT) {
        $tStart = max(0, $center - $length);
        $tEnd = $center + $length;

        $truncated = mb_substr($expr, $tStart, $tEnd - $tStart);

        if ($tEnd < mb_strlen($expr)) {
            $truncated = $truncated . ' ' . $elipsis;
        }

        if ($tStart > 0) {
            $truncated = $elipsis . ' ' . $truncated;
        }

        return $truncated;
    }
}
