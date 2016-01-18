<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Model\SuiteCollection;

class SuiteCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->suite1 = $this->prophesize('PhpBench\Model\Suite');
        $this->suite2 = $this->prophesize('PhpBench\Model\Suite');
        $this->suite3 = $this->prophesize('PhpBench\Model\Suite');
    }

    /**
     * It should add suites.
     */
    public function testAddSuite()
    {
        $collection = new SuiteCollection();
        $collection->addSuite($this->suite1->reveal());
        $this->assertSame(array(
            $this->suite1->reveal(),
        ), $collection->getSuites());
    }

    /**
     * It should merge another colleciton into itself.
     */
    public function testMergeCollection()
    {
        $collection = new SuiteCollection(array(
            $this->suite1->reveal(),
        ));
        $newCollection = new SuiteCollection(array(
            $this->suite2->reveal(),
            $this->suite3->reveal(),
        ));

        $collection->mergeCollection($newCollection);

        $this->assertEquals(array(
            $this->suite1->reveal(),
            $this->suite2->reveal(),
            $this->suite3->reveal(),
        ), $collection->getSuites());
    }
}
