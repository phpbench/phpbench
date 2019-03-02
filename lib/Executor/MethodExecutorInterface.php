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

namespace PhpBench\Executor;

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;

interface MethodExecutorInterface
{
    public function executeMethods(BenchmarkMetadata $benchmark, array $methods): void;
}
