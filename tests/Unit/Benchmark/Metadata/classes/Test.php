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

use PhpBench\Benchmark\Metadata\Annotations as PhpBench;

/**
 * Class used to test the annotation import.
 */
class Test
{
    /**
     * @PhpBench\Subject()
     */
    public function test()
    {
    }
}
