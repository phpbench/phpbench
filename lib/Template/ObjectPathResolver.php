<?php

namespace PhpBench\Template;

use PhpBench\Template\Exception\CouldNotResolvePath;
use ReflectionClass;
use RuntimeException;
use function class_parents;

final class ObjectPathResolver
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

    public function resolvePath(string $classFqn): string
    {
        $path = $this->tryToResolve($classFqn);

        if ($path) {
            return $path;
        }

        $reflectionClass = new ReflectionClass($classFqn);

        foreach ($reflectionClass->getInterfaceNames() as $interfaceName) {
            try {
                $path = $this->tryToResolve($interfaceName);
            } catch (CouldNotResolvePath $path) {
                continue;
            }

            if ($path) {
                return $path;
            }
        }

        while ($parentClass = $reflectionClass->getParentClass()) {
            return $this->resolvePath($parentClass->getName());
        }

        throw new CouldNotResolvePath(sprintf(
            'Could not find template for class "%s" (primary location would be "%s")',
            $classFqn, $this->classToPath($classFqn)
        ));
    }

    private function tryToResolve(string $classFqn): ?string
    {
        $path = $this->classToPath($classFqn);

        if (file_exists($path)) {
            return $path;
        }

        return null;
    }

    private function classToPath(string $classFqn): string
    {
        foreach ($this->prefixMap as $prefix => $pathPrefix) {
            if (false !== strpos($classFqn, $prefix)) {
                return sprintf('%s/%s.phtml', rtrim($pathPrefix, '/'), ltrim(str_replace('\\', '/', substr($classFqn, strlen($prefix))), '/'));
            }
        }

        throw new CouldNotResolvePath(sprintf(
            'Class "%s" does is not mapped to a template. Only classes starting with the following prefixes are templatable: "%s"',
            $classFqn,
            implode('", "', array_keys($this->prefixMap))
        ));
    }
}
