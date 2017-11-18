<?php

namespace PhpBench\Extensions\Elastic\Tests\Unit\Encoder;

use PHPUnit\Framework\TestCase;
use PhpBench\Extensions\Elastic\Encoder\DocumentEncoder;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Model\Suite;

class DocumentEncoderTest extends TestCase
{
    /**
     * @var DocumentEncoder
     */
    private $encoder;

    public function setUp()
    {
        $this->encoder = new DocumentEncoder();
    }

    protected function createTestSuite(): Suite
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

    public function testEncode(): array
    {
        $suite = $this->createTestSuite();

        return $this->encoder->documentsFromSuite($suite);
    }
}
