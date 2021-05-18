<?php

namespace PhpBench\Template;

use PhpBench\Template\ObjectPathResolver\ReflectionObjectPathResolver;
use RuntimeException;
use function ob_get_clean;
use function ob_start;

final class ObjectRenderer 
{
    /**
     * @var ObjectPathResolver
     */
    private $resolver;

    public function __construct(ObjectPathResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function render(object $object): string
    {
        $path = $this->resolver->resolvePath($object);

        if (!file_exists($path)) {
            throw new RuntimeException(sprintf(
                'Path resolver returned non-existing path "%s"',
                $path
            ));
        }

        ob_start();
        require $path;
        return ob_get_clean();
    }
}
