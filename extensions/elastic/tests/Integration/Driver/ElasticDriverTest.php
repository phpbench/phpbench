<?php

namespace PhpBench\Extensions\Elastic\Tests\Integration\Driver;

use PHPUnit\Framework\TestCase;
use PhpBench\Extensions\Elastic\Driver\ElasticClient;
use PhpBench\Extensions\Elastic\Driver\ElasticDriver;
use PhpBench\Model\SuiteCollection;
use PhpBench\Tests\Util\TestUtil;
use PhpBench\Extensions\Elastic\Tests\Integration\ElasticTestCase;

class ElasticDriverTest extends ElasticTestCase
{
    /**
     * @var ElasticDriver
     */
    private $driver;

    public function setUp()
    {
        $this->driver = new ElasticDriver($this->createClient());
    }

    public function testPersistAndFetch()
    {
        $uuid = 'abcd';
        $suiteCollection = $this->createSuiteCollection($uuid);
        $this->driver->store($suiteCollection);

        $persistedSuiteCollection = $this->driver->fetch($suiteCollection->first()->getUuid());
        $persistedSuite = $persistedSuiteCollection->first();
        $this->assertEquals($uuid, $persistedSuite->getUuid());
    }

    public function testHistory()
    {
        $suiteCollection = $this->createSuiteCollection('a');
        $this->driver->store($suiteCollection);
        $suiteCollection = $this->createSuiteCollection('b');
        $this->driver->store($suiteCollection);

        $this->driver->history();
    }

    private function createSuiteCollection(string $uuid): SuiteCollection
    {
        $suite = TestUtil::createSuite([
            'uuid' => $uuid,
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

        return new SuiteCollection([ $suite ]);
    }
}
