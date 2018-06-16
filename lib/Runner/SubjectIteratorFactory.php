<?php

namespace PhpBench\Runner;

class SubjectIteratorFactory
{
    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    public function __construct(MetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    public function subjectIterator(string $path, array $filters = []): SubjectIterator
    {
        $fileIterator = new BenchmarkFileIterator($path);

        return new SubjectIterator($fileIterator, $this->metadataFactory, $filters);
    }
}
