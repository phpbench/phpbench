<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\Factory;
use Symfony\Component\Finder\Finder;

/**
 * This class finds a benchmark (or benchmarks depending on the path), loads
 * their metadata and builds a collection of BenchmarkMetadata instances.
 */
class BenchmarkFinder
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @param Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Build the BenchmarkMetadata collection.
     *
     * @param string $path
     * @param array $subjectFilter
     * @param array $groupFilter
     */
    public function findBenchmarks($path, array $filters = array(), array $groupFilter = array())
    {
        $finder = new Finder();
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'File or directory "%s" does not exist (cwd: %s)',
                $path,
                getcwd()
            ));
        }

        if (is_dir($path)) {
            $finder->in($path)
                ->name('*Bench.php');
        } else {
            // the path is already a file, just restrict the finder to that.
            $finder->in(dirname($path))
                ->depth(0)
                ->name(basename($path));
        }

        $benchmarks = array();

        foreach ($finder as $file) {
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

            if ($filters) {
                $benchmark->filterSubjectNames($filters);
            }

            if (false === $benchmark->hasSubjects()) {
                continue;
            }

            $benchmarks[] = $benchmark;
        }

        return $benchmarks;
    }
}
