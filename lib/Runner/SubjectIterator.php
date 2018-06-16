<?php

namespace PhpBench\Runner;

use Generator;
use IteratorAggregate;
use PhpBench\Benchmark\Metadata\MetadataFactory;
use SplFileInfo;
use PhpBench\Runner\BenchmarkFileIterator;

class SubjectIterator implements IteratorAggregate
{
    /**
     * @var array
     */
    private $filters;

    /**
     * @var MetadataFactory
     */
    private $metadataFactory;

    /**
     * @var BenchmarkFileIterator<SplFileInfo>
     */
    private $benchmarkFiles;

    public function __construct(
        BenchmarkFileIterator $benchmarkFiles,
        MetadataFactory $metadataFactory,
        array $filters
    )
    {
        $this->metadataFactory = $metadataFactory;
        $this->filters = $filters;
        $this->benchmarkFiles = $benchmarkFiles;
    }

    public function getIterator()
    {
        while (true) {
            foreach ($this->benchmarkFiles as $file) {
                $metadata = $this->metadataFactory->getMetadataForFile($file->getPathname());

                foreach ($metadata->getSubjects() as $subject) {
                    yield $subject;
                }
            }
        }
    }

}
