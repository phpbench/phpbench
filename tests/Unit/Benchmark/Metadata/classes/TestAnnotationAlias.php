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

namespace PhpBench\Tests\Unit\Benchmark\Metadata\classes;

use PhpBench\Benchmark\Metadata\Annotations\Revs as R;

/**
 * Class used to test detection of aliased annotation imports.
 */
class TestAnnotationAlias
{
    /**
     * @R(10)
     */
    public function test(): void
    {
    }
}
