<?php

namespace PhpBench\Template;

use PhpBench\Template\Exception\CouldNotResolvePath;
use ReflectionClass;
use RuntimeException;
use function class_parents;

interface ObjectPathResolver
{
    /**
     * @return string[]
     */
    public function resolvePaths(object $object): array;
}
