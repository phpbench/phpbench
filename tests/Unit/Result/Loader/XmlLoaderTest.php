<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Result\Loader;

use PhpBench\Result\Loader\XmlLoader;
use PhpBench\Tests\Unit\Result\Dumper\XmlDumperTest;

class XmlLoaderTest extends XmlDumperTest
{
    public function setUp()
    {
        parent::setUp();
        $this->loader = new XmlLoader();
    }

    /**
     * Its should load an XML document and return the suite
     */
    public function testLoad()
    {
        $result = $this->testDump();
        $suite = $this->loader->load($result);
        $this->assertEquals(
            $this->getSuite(),
            $suite
        );
    }

    /**
     * Its should thow an exception if the XML is invalid
     *
     * @expectedException RuntimeException
     */
    public function testLoadInvalid()
    {
        $this->loader->load('hai');
    }
}
