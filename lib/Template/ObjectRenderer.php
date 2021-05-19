<?php

namespace PhpBench\Template;

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

    public function __construct(ObjectPathResolver $resolver, array $templatePaths)
    {
        $this->resolver = $resolver;
        $this->templatePaths = $templatePaths;
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

                require $absolutePath;

                return ob_get_clean();
            }
        }

        throw new RuntimeException(sprintf(
            'Could not resolve path for object "%s", tried paths "%s"',
            get_class($object), implode('", "', $tried)
        ));
    }
}
