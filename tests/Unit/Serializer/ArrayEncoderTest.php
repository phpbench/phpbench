<?php

namespace PhpBench\Tests\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use PhpBench\Serializer\ArrayEncoder;
use PhpBench\Tests\Util\TestUtil;

class ArrayEncoderTest extends TestCase
{
    /**
     * @var ArrayEncoder
     */
    private $encoder;

    public function setUp()
    {
        $this->encoder = new ArrayEncoder();
    }

    public function testArrayEncoder()
    {
        $suite = TestUtil::createSuite();
        $result = $this->encoder->encodeSuite($suite);
        var_dump($result);
    }
}
