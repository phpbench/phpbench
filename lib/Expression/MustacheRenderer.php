<?php

namespace PhpBench\Expression;

use Closure;

use function preg_replace_callback;

final class MustacheRenderer
{
    public function render(string $template, Closure $closure): string
    {
        return preg_replace_callback('{({{)(.*?)(}})}', function (array $matches) use ($closure) {
            $match = trim($matches[2]);

            return $closure($match);
        }, $template);
    }
}
