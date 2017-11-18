<?php

namespace PhpBench\Extensions\Elastic\Tests\Integration\Driver;

use PHPUnit\Framework\TestCase;
use PhpBench\Extensions\Elastic\Driver\ElasticClient;
use PhpBench\Extensions\Elastic\Driver\ElasticDriver;
use PhpBench\Model\SuiteCollection;
use PhpBench\Tests\Util\TestUtil;

class ElasticDriverTest extends TestCase
{
    /**
     * @var ElasticDriver
     */
    private $driver;

    public function setUp()
    {
        $client = new ElasticClient([]);
        $this->driver = new ElasticDriver($client);
    }

    public function testPersistAndFetch()
    {
        $suite = TestUtil::createSuite([
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
        $suiteCollection = new SuiteCollection([ $suite ]);

        $this->driver->store($suiteCollection);

        $persistedSuite = $this->driver->fetch(1);
        $this->assertEquals($persistedSuite, $suite);
    }
}
