<?php

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

    public function testLoad()
    {
        $result = $this->testDump();
        $suite = $this->loader->load($result);
        $this->assertEquals(
            $this->getSuite(),
            $suite
        );
    }
}
