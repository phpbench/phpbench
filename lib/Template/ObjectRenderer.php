<?php

namespace PhpBench\Template;

use PhpBench\Template\ObjectPathResolver\ReflectionObjectPathResolver;
use function ob_get_clean;
use function ob_start;

final class ObjectRenderer 
{
    /**
     * @var ObjectPathResolver
     */
    private $resolver;

    public function __construct(ReflectionObjectPathResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function render(object $object): string
    {
        $path = $this->resolver($object);
        ob_start();
        require $path;
        return ob_get_clean();
    }
}