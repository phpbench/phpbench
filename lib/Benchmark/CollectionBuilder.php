<?php

/*
 * This file is part of the PHP Bench package
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
    private $subjectBuilder;

    public function __construct(SubjectBuilder $subjectBuilder, Finder $finder = null, $baseDir = null)
    {
        $this->subjectBuilder = $subjectBuilder;
        $this->finder = $finder ?: new Finder();
        $this->baseDir = $baseDir;
    }

    public function buildCollection($path)
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
            $this->finder->in(dirname($path))
                ->name(basename($path));
        }

        $benchmarks = array();

        foreach ($this->finder as $file) {
            if (!is_file($file)) {
                continue;
            }

            require_once $file->getRealPath();
            $classFqn = static::getClassNameFromFile($file->getRealPath());
            $this->subjectBuilder->buildSubjects($benchmark);
            $benchmarks[] = $benchmark;
        }

        return new Collection($benchmarks);
    }
}
