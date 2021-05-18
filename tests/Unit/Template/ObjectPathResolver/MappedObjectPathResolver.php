<?php

namespace PhpBench\Tests\Unit\Template\ObjectPathResolver;

use PhpBench\Template\Exception\CouldNotResolvePath;
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

    public function resolvePath(object $object): string
    {
        $fqn = get_class($object);
        if (isset($this->map[$fqn])) {
            return $this->map[$fqn];
        }

        throw new CouldNotResolvePath(sprintf(
            'No path is explicitly mapped for class "%s"',
            get_class($object)
        ));
    }
}
