<?php

namespace PhpBench\Template\ObjectPathResolver;

use PhpBench\Template\ObjectPathResolver;

class MappedObjectPathResolver implements ObjectPathResolver
{
    /**
     * @var array
     */
    private $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function resolvePaths(object $object): array
    {
        $fqn = get_class($object);

        if (isset($this->map[$fqn])) {
            return [$this->map[$fqn]];
        }

        return [];
    }
}
