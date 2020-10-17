<?php

namespace PhpBench\Tests\Util;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;

class BenchmarkMetadataBuiler
{
    /**
     * @var SubjectMetadataBuilder[]
     */
    private $subjects = [];

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    private function __construct(string $path, string $name)
    {
        $this->name = $name;
        $this->path = $path;
    }

    public static function create(string $path, string $name): self
    {
        return new self($path, $name);
    }

    public function subject(string $name): SubjectMetadataBuilder
    {
        $builder = new SubjectMetadataBuilder($this, $name);
        $this->subjects[] = $builder;

        return $builder;
    }

    public function build(): BenchmarkMetadata
    {
        $benchmark = new BenchmarkMetadata(
            $this->path,
            $this->name
        );

        array_map(function (SubjectMetadataBuilder $builder) use ($benchmark) {
            $builder->build($benchmark);
        }, $this->subjects);

        return $benchmark;
    }
}
