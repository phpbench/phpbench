<?php

namespace PhpBench\Template\ObjectPathResolver;

use PhpBench\Template\ObjectPathResolver;
use ReflectionClass;

final class ReflectionObjectPathResolver implements ObjectPathResolver
{
    /**
     * @var array<string,string>
     */
    private $prefixMap;

    /**
     * @param array<string,string> $prefixMap
     */
    public function __construct(array $prefixMap)
    {
        $this->prefixMap = $prefixMap;
    }

    /**
     * @return string[]
     */
    public function resolvePaths(object $object): array
    {
        $paths = [ $this->classToPath(get_class($object)) ];

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
            if (false !== strpos($classFqn, $prefix)) {
                return sprintf('%s/%s.phtml', rtrim($pathPrefix, '/'), ltrim(str_replace('\\', '/', substr($classFqn, strlen($prefix))), '/'));
            }
        }

        return null;
    }
}
