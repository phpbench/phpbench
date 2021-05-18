<?php

namespace PhpBench\Template;

use PhpBench\Template\Exception\CouldNotResolvePath;
use ReflectionClass;
use RuntimeException;
use function class_parents;

interface ObjectPathResolver
{
    public function resolvePath(object $object): string;
}
