<?php

namespace PhpBench\Benchmark\Remote;

class ClassMetadatas
{
    /**
     * @var array
     */
    private $classHierarchy;

    public function __construct(array $classHierarchy)
    {
        $this->classHierarchy = $classHierarchy;
    }
}
