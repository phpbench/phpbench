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

namespace PhpBench\Tests\Unit\Model\Result;

use PhpBench\Model\Result\RejectionCountResult;
use PhpBench\Tests\TestCase;

class RejectionCountResultTest extends TestCase
{
    public function testMemoryGreaterEqualThan(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Rejection count must be greater or equal to 0');
        new RejectionCountResult(-1);
    }
}
