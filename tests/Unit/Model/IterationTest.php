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

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Model\Iteration;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Variant;
use PhpBench\Tests\Util\TestUtil;
use PHPUnit\Framework\TestCase;

class IterationTest extends TestCase
{
    private $iteration;

    protected function setUp(): void
    {
        $this->variant = $this->prophesize(Variant::class);
        $this->iteration = new Iteration(
            0,
            $this->variant->reveal()
        );
    }

    /**
     * It should have getters that return its values.
     */
    public function testGetters()
    {
        $this->assertEquals($this->variant->reveal(), $this->iteration->getVariant());
    }

    /**
     * It should return the revolution time.
     */
    public function testGetRevTime()
    {
        $iteration = new Iteration(1, $this->variant->reveal(), TestUtil::createResults(100));
        $this->variant->getRevolutions()->willReturn(100);
        $this->assertEquals(1, $iteration->getResult(TimeResult::class)->getRevTime(100));
    }
}
