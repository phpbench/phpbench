<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Benchmark;

use Generator;
use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\MetadataFactory;
use PhpBench\PhpBench;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * This class finds a benchmark (or benchmarks depending on the path), loads
 * their metadata and builds a collection of BenchmarkMetadata instances.
 */
class BenchmarkFinder
{
    /**
     * @var MetadataFactory
     */
    private $factory;

    public function __construct(MetadataFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param array<string> $subjectFilter
     * @param array<string> $groupFilter
     *
     * @return Generator<BenchmarkMetadata>
     */
    public function findBenchmarks(array $paths, array $subjectFilter = [], array $groupFilter = []): Generator
    {
        foreach ($this->findFiles($paths) as $file) {
            if (!is_file($file)) {
                continue;
            }

            $benchmark = $this->factory->getMetadataForFile($file->getPathname());

            if (null === $benchmark) {
                continue;
            }

            if ($groupFilter) {
                $benchmark->filterSubjectGroups($groupFilter);
            }

            if ($subjectFilter) {
                $benchmark->filterSubjectNames($subjectFilter);
            }

            if (false === $benchmark->hasSubjects()) {
                continue;
            }

            yield $benchmark;
        }
    }

    /**
     * @return Generator<SplFileInfo>
     */
    private function findFiles(array $paths): Generator
    {
        $finder = new Finder();
        $search = false;

        foreach ($paths as $path) {
            $path = PhpBench::normalizePath($path);

            if (!file_exists($path)) {
                throw new \InvalidArgumentException(sprintf(
                    'File or directory "%s" does not exist (cwd: %s)',
                    $path,
                    getcwd()
                ));
            }

            if (is_dir($path)) {
                $search = true;
                $finder->in($path)->name('*.php');

                continue;
            }

            if (is_file($path)) {
                yield new SplFileInfo($path);

                continue;
            }
        }

        if ($search === false) {
            return;
        }

        foreach ($finder as $file) {
            yield $file;
        }
    }
}
