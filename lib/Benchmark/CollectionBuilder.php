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

use Symfony\Component\Finder\Finder;

class CollectionBuilder
{
    private $finder;
    private $baseDir;
    private $benchmarkBuilder;

    public function __construct(BenchmarkBuilder $benchmarkBuilder, Finder $finder = null, $baseDir = null)
    {
        $this->benchmarkBuilder = $benchmarkBuilder;
        $this->finder = $finder ?: new Finder();
        $this->baseDir = $baseDir;
    }

    public function buildCollection($path, array $subjectFilter = array(), array $groupFilter = array())
    {
        if ($this->baseDir && '/' !== substr($path, 0, 1)) {
            $path = realpath($this->baseDir . '/' . $path);
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'File or directory "%s" does not exist',
                $path
            ));
        }

        if (is_dir($path)) {
            $this->finder->in($path)
                ->name('*Bench.php');
        } else {
            // the path is already a file, just restrict the finder to that.
            $this->finder->in(dirname($path))
                ->depth(0)
                ->name(basename($path));
        }

        $benchmarks = array();

        foreach ($this->finder as $file) {
            if (!is_file($file)) {
                continue;
            }

            $benchmark = $this->benchmarkBuilder->build($file->getPathname(), $subjectFilter, $groupFilter);

            if (null === $benchmark) {
                continue;
            }

            $benchmarks[] = $benchmark;
        }

        return new Collection($benchmarks);
    }
}
