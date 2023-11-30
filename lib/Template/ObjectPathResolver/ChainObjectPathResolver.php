<?php

namespace PhpBench\Template\ObjectPathResolver;

use PhpBench\Template\ObjectPathResolver;

class ChainObjectPathResolver implements ObjectPathResolver
{
    public function __construct(
        /**
         * @var ObjectPathResolver[]
         */
        private readonly array $objectPathResolvers
    ) {
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
