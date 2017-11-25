<?php

namespace PhpBench\Extensions\Elastic\Tests\Integration\Driver;

use PHPUnit\Framework\TestCase;
use PhpBench\Extensions\Elastic\Driver\ElasticClient;

class Driver extends TestCase
{
    public function testPutGet()
    {
        $data = [
            'data' => 'yes please'
        ];

        $client = $this->createClient();
        $client->put(1234, $data);
        $document = $client->get(1234);

        $this->assertEquals($data, $document['_source']);
    }
    protected function createClient(): ElasticClient
    {
        return new ElasticClient([
            'port' => getenv('PHPBENCH_ELASTIC_PORT') ?: 9200
        ]);
    }
}
