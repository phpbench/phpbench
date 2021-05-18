<?php

namespace PhpBench\Template\ObjectPathResolver;

use PhpBench\Template\Exception\CouldNotResolvePath;
use PhpBench\Template\ObjectPathResolver;
use PhpBench\Template\ObjectPathResolver\ReflectionObjectPathResolver;
use ReflectionClass;
use RuntimeException;
use function class_parents;

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

    public function resolvePath(object $object): string
    {
        $path = $this->tryToResolve(get_class($object));

        if ($path) {
            return $path;
        }

        $reflectionClass = new ReflectionClass($object);

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

        $parentClass = $reflectionClass;
        while ($parentClass = $parentClass->getParentClass()) {
            try {
                return $this->tryToResolve($parentClass->getName());
            } catch(CouldNotResolvePath $_) {
            }
        }

        throw new CouldNotResolvePath(sprintf(
            'Could not find template for class "%s" (primary location would be "%s")',
            get_class($object), $this->classToPath(get_class($object))
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
