<?php

namespace PhpBench\Reflection;

use PhpBench\Model\ParameterSetsCollection;

interface ReflectorInterface
{
    /**
     * Return an array of ReflectionClass instances for the given file. The
     * first ReflectionClass is the class contained in the given file (there
     * may be only one) additional ReflectionClass instances are the ancestors
     * of this first class.
     */
    public function reflect(string $file): ReflectionHierarchy;

    /**
     * Return the parameter sets for the benchmark container in the given file.
     *
     * @param array<string> $paramProviders
     */
    public function getParameterSets(string $file, array $paramProviders): ParameterSetsCollection;
}
