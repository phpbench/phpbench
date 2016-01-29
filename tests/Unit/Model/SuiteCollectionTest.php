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
        $this->suite = $this->prophesize('PhpBench\Model\Suite');
    }

    /**
     * It should add suites.
     */
    public function testAddSuite()
    {
        $collection = new SuiteCollection();
        $collection->addSuite($this->suite->reveal());
        $this->assertSame(array(
            $this->suite->reveal(),
        ), $collection->getSuites());
    }
}
