<?php

namespace PhpBench\Tests\Util;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;

class SubjectMetadataBuilder
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var BenchmarkMetadataBuiler
     */
    private $parent;

    public function __construct(BenchmarkMetadataBuiler $parent, string $name)
    {
        $this->name = $name;
        $this->parent = $parent;
    }

    public function end(): BenchmarkMetadataBuiler
    {
        return $this->parent;
    }

    public function build(BenchmarkMetadata $benchmark): SubjectMetadata
    {
        return $benchmark->getOrCreateSubject($this->name);
    }
}
