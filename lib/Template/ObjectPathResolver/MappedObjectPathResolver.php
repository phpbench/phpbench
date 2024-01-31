<?php

namespace PhpBench\Template\ObjectPathResolver;

use PhpBench\Template\ObjectPathResolver;

class MappedObjectPathResolver implements ObjectPathResolver
{
    /**
     * @param array<class-string, string> $map
     */
    public function __construct(private array $map)
    {
    }

    public function resolvePaths(object $object): array
    {
        $fqn = $object::class;

        if (isset($this->map[$fqn])) {
            return [$this->map[$fqn]];
        }

        return [];
    }
}
