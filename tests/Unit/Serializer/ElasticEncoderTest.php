<?php

namespace PhpBench\Tests\Unit\Serializer;

use PHPUnit\Framework\TestCase;
use PhpBench\Serializer\ElasticEncoder;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Model\Suite;

class ElasticEncoderTest extends TestCase
{
    /**
     * @var DocumentEncoder
     */
    private $encoder;

    public function setUp()
    {
        $this->encoder = new ElasticEncoder();
    }

    public function testAggregationsFromSuite()
    {
        $suite = $this->createTestSuite();

        $documents = $this->encoder->aggregationsFromSuite($suite);
        $this->assertInternalType('array', $documents);
        $this->assertCount(2, $documents);
        $document = reset($documents);
        $this->assertEquals('1', $document['suite']);
        $this->assertEquals(2, $document['stats']['min']);
    }

    public function testIterationsFromSuite()
    {
        $suite = $this->createTestSuite();

        $documents = $this->encoder->iterationsFromSuite($suite);
        $this->assertCount(4, $documents);
        $document = reset($documents);
        $this->assertEquals('1', $document['suite']);
        $this->assertEquals('benchOne', $document['subject']);
        $this->assertEquals('TestBench', $document['class']);
        $this->assertEquals(0, $document['variant']);
        $this->assertEquals(0, $document['iteration']);
    }

    private function createTestSuite(): Suite
    {
        return TestUtil::createSuite([
            'uuid' => '1',
            'subjects' => ['benchOne', 'benchTwo'],
            'groups' => ['one', 'two'],
            'parameters' => [
                'one' => 'two',
                'three' => ['one', 'two'],
            ],
            'env' => [
                'system' => [
                    'os' => 'linux',
                    'memory' => 8096,
                    'distribution' => 'debian',
                ],
                'vcs' => [
                    'system' => 'git',
                    'branch' => 'foo',
                ],
            ],
        ]);
    }

}
