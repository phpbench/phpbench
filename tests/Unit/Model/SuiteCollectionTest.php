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
use PhpBench\Tests\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class SuiteCollectionTest extends TestCase
{
    /**
     * @var ObjectProphecy<Suite>
     */
    private $suite1;
    /**
     * @var ObjectProphecy<Suite>
     */
    private $suite2;
    /**
     * @var ObjectProphecy<Suite>
     */
    private $suite3;

    protected function setUp(): void
    {
        $this->suite1 = $this->prophesize(Suite::class);
        $this->suite2 = $this->prophesize(Suite::class);
        $this->suite3 = $this->prophesize(Suite::class);
    }

    public function testReturnWithFirstOnly(): void
    {
        $collection = (new SuiteCollection([
            $this->suite1->reveal(),
            $this->suite2->reveal(),
            $this->suite3->reveal(),
        ]))->firstOnly();
        self::assertCount(1, $collection);
        self::assertSame($this->suite1->reveal(), $collection->getSuites()[0]);
    }

    /**
     * It should add suites.
     */
    public function testAddSuite(): void
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
    public function testMergeCollection(): void
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
