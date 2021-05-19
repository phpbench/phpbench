<?php

namespace PhpBench\Template;

interface ObjectPathResolver
{
    /**
     * @return string[]
     */
    public function resolvePaths(object $object): array;
}
