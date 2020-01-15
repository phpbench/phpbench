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

use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PHPUnit\Framework\TestCase;

class SuiteCollectionTest extends TestCase
{
    protected function setUp(): void
    {
        $this->suite1 = $this->prophesize(Suite::class);
        $this->suite2 = $this->prophesize(Suite::class);
        $this->suite3 = $this->prophesize(Suite::class);
    }

    /**
     * It should add suites.
     */
    public function testAddSuite()
    {
        $collection = new SuiteCollection();
        $collection->addSuite($this->suite1->reveal());
        $this->assertSame([
            $this->suite1->reveal(),
        ], $collection->getSuites());
    }

    /**
     * It should merge another colleciton into itself.
     */
    public function testMergeCollection()
    {
        $collection = new SuiteCollection([
            $this->suite1->reveal(),
        ]);
        $newCollection = new SuiteCollection([
            $this->suite2->reveal(),
            $this->suite3->reveal(),
        ]);

        $collection->mergeCollection($newCollection);

        $this->assertEquals([
            $this->suite1->reveal(),
            $this->suite2->reveal(),
            $this->suite3->reveal(),
        ], $collection->getSuites());
    }
}
