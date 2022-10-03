<?php

namespace PhpBench\Template;

use Exception;
use PhpBench\Template\Exception\CouldNotFindTemplateForObject;

use function ob_end_clean;
use function ob_get_clean;
use function ob_start;

final class ObjectRenderer
{
    private $idCounter = 0;

    /**
     * @var ObjectPathResolver
     */
    private $resolver;

    /**
     * @var array
     */
    private $templatePaths;

    /**
     * @var TemplateService
     */
    private $container;

    public function __construct(ObjectPathResolver $resolver, array $templatePaths, TemplateService $container)
    {
        $this->resolver = $resolver;
        $this->templatePaths = $templatePaths;
        $this->container = $container;
    }

    public function render(object $object): string
    {
        $this->idCounter++;

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
                    $this->doRequire($object, $absolutePath);
                } catch (Exception $e) {
                    ob_end_clean();

                    throw $e;
                }

                return (string)ob_get_clean();
            }
        }

        throw new CouldNotFindTemplateForObject(sprintf(
            'Could not resolve path for object "%s", tried paths "%s"',
            get_class($object),
            implode('", "', $tried)
        ));
    }

    /**
     * @return mixed
     */
    public function __get(string $serviceName)
    {
        return $this->container->get($serviceName);
    }

    private function doRequire(object $object, string $absolutePath): void
    {
        $id = sprintf('phpbench-component-%s', $this->idCounter);

        require $absolutePath;
    }
}
