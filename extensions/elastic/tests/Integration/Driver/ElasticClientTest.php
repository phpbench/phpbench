<?php

namespace PhpBench\Extensions\Elastic\Tests\Integration\Driver;

use PHPUnit\Framework\TestCase;
use PhpBench\Extensions\Elastic\Driver\ElasticClient;

class ElasticClientTest extends TestCase
{
    public function testPutGet()
    {
        $data = [
            'data' => 'yes please'
        ];

        $client = $this->createClient();
        $client->put(ElasticClient::TYPE_VARIANT, 1234, $data);
        $document = $client->get(ElasticClient::TYPE_VARIANT, 1234);

        $this->assertEquals($data, $document['_source']);
    }
    protected function createClient(): ElasticClient
    {
        return new ElasticClient([
            'port' => getenv('PHPBENCH_ELASTIC_PORT') ?: 9200
        ]);
    }
}
