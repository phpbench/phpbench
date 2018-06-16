<?php

namespace PhpBench\Runner;

use Generator;
use IteratorAggregate;
use PhpBench\PhpBench;
use Symfony\Component\Finder\Finder;

class BenchmarkFileIterator implements IteratorAggregate
{
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getIterator()
    {
        $finder = new Finder();
        $path = PhpBench::normalizePath($this->path);

        if (false === file_exists($path)) {
            throw new \InvalidArgumentException(sprintf(
                'File or directory "%s" does not exist (cwd: %s)',
                $path,
                getcwd()
            ));
        }

        $this->configureFinder($path, $finder);

        foreach ($finder as $file) {
            yield $file;
        }
    }

    private function configureFinder(string $path, Finder $finder)
    {
        $finder->files();

        if (is_dir($path)) {
            $finder->in($path)->name('*.php');
            return;
        }

        $finder->in(dirname($path));
        $finder->depth(0);
        $finder->name(basename($path));
    }
}
