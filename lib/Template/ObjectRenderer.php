<?php

namespace PhpBench\Template;

use Exception;
use function ob_clean;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use RuntimeException;

final class ObjectRenderer
{
    /**
     * @var ObjectPathResolver
     */
    private $resolver;

    /**
     * @var array
     */
    private $templatePaths;

    /**
     * @var array
     */
    private $serviceMap;

    public function __construct(ObjectPathResolver $resolver, array $templatePaths, array $serviceMap)
    {
        $this->resolver = $resolver;
        $this->templatePaths = $templatePaths;
        $this->serviceMap = $serviceMap;
    }

    public function render(object $object): string
    {
        $paths = $this->resolver->resolvePaths($object);
        $tried = [];

        foreach ($paths as $path) {
            foreach ($this->templatePaths as $templatePath) {
                $absolutePath = $templatePath . '/' . $path;

                if (!file_exists($absolutePath)) {
                    $tried[] = $absolutePath;

                    continue;
                }

                ob_start();

                try {
                    require $absolutePath;
                } catch (Exception $e) {
                    ob_end_clean();
                    throw $e;
                }

                return ob_get_clean();
            }
        }

        throw new RuntimeException(sprintf(
            'Could not resolve path for object "%s", tried paths "%s"',
            get_class($object), implode('", "', $tried)
        ));
    }

    /**
     * @template T
     * @param class-string<T>
     * @return T
     */
    public function __get(string $serviceFqn)
    {
        if (!isset($this->serviceMap[$serviceFqn])) {
            throw new RuntimeException(sprintf(
                'Unknown template service "%s", known template services: "%s"',
                $serviceFqn, implode('", "', array_keys($this->serviceMap))
            ));
        }

        return $this->serviceMap[$serviceFqn];
    }
}
