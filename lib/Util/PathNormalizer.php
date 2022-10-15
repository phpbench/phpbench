<?php

namespace PhpBench\Util;

use PhpBench\Path\Path;
use Webmozart\Glob\Glob;

class PathNormalizer
{
    /**
     * @param string[] $paths
     *
     * @return string[]
     */
    public static function normalizePaths(string $baseDir, array $paths): array
    {
        return array_merge(...array_map(static function (string $path) use ($baseDir) {
            $path = Path::isAbsolute($path) ? $path : Path::join(...[$baseDir, $path]);

            if (self::isGlob($path)) {
                $globPaths = Glob::glob($path);

                if (empty($globPaths)) {
                    return [];
                }

                return $globPaths;
            }

            return [ $path ];
        }, $paths));
    }

    private static function isGlob(string $path): bool
    {
        return str_contains($path, '*') || str_contains($path, '?');
    }
}
