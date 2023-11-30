<?php

namespace PhpBench\Template\ObjectPathResolver;

use PhpBench\Template\ObjectPathResolver;
use ReflectionClass;

final class ReflectionObjectPathResolver implements ObjectPathResolver
{
    /**
     * @param array<string,string> $prefixMap
     */
    public function __construct(private readonly array $prefixMap)
    {
    }

    /**
     * @return string[]
     */
    public function resolvePaths(object $object): array
    {
        $paths = [ $this->classToPath($object::class) ];

        $reflectionClass = new ReflectionClass($object);

        $parentClass = $reflectionClass;

        while ($parentClass = $parentClass->getParentClass()) {
            $path = $this->classToPath($parentClass->getName());

            if (!$path) {
                continue;
            }
            $paths[] = $path;
        }

        return $paths;
    }

    private function classToPath(string $classFqn): ?string
    {
        foreach ($this->prefixMap as $prefix => $pathPrefix) {
            if (str_contains($classFqn, $prefix)) {
                return sprintf('%s/%s.phtml', rtrim($pathPrefix, '/'), ltrim(str_replace('\\', '/', substr($classFqn, strlen($prefix))), '/'));
            }
        }

        return null;
    }
}
