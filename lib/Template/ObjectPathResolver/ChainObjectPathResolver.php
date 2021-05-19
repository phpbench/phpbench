<?php

namespace PhpBench\Template\ObjectPathResolver;

use PhpBench\Template\ObjectPathResolver;

class ChainObjectPathResolver implements ObjectPathResolver
{
    /**
     * @var ObjectPathResolver[]
     */
    private $objectPathResolvers;

    public function __construct(array $objectPathResolvers)
    {
        $this->objectPathResolvers = $objectPathResolvers;
    }

    /**
     * {@inheritDoc}
     */
    public function resolvePaths(object $object): array
    {
        foreach ($this->objectPathResolvers as $resolver) {
            $paths = $resolver->resolvePaths($object);

            if ($paths) {
                return $paths;
            }
        }

        return [];
    }
}
