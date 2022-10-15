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
use PhpBench\Path\Path;
use Psr\Log\LoggerInterface;
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

    /**
     * @var string
     */
    private $cwd;

    /**
     * @var string
     */
    private $benchPattern;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(MetadataFactory $factory, string $cwd, LoggerInterface $logger, ?string $benchPattern = null)
    {
        $this->factory = $factory;
        $this->cwd = $cwd;
        $this->benchPattern = $benchPattern;
        $this->logger = $logger;
    }

    /**
     * @param array<string> $paths
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
            $path = Path::makeAbsolute($path, $this->cwd);

            if (!file_exists($path)) {
                throw new \InvalidArgumentException(sprintf(
                    'File or directory "%s" does not exist (cwd: %s)',
                    $path,
                    $this->cwd
                ));
            }

            if (is_dir($path)) {
                $search = true;
                $finder->in($path)->name($this->benchPattern === null ? '*.php' : $this->benchPattern);

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
            assert($file instanceof SplFileInfo);

            if ($this->benchPattern === null && substr($file->getFilename(), -9) !== 'Bench.php') {
                $this->logger->warning(sprintf(
                    'File "%s" has been identified as a benchmark file but it does not end with ' .
                    '`Bench.php`. This behavior is incorrect and will be fixed in a future version. ' .
                    'Set `runner.file_pattern` to `*Bench.php` in `phpbench.json` to avoid this.',
                    $file->getFilename()
                ));
            }

            yield $file;
        }
    }
}
