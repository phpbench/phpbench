<?php

namespace PhpBench\Tests\Integration\Storage\Driver\Elastic;

use PHPUnit\Framework\TestCase;
use PhpBench\Storage\Driver\Elastic\ElasticClient;

class ElasticClientTest extends TestCase
{
    public function testElasticClient()
    {
        $client = new ElasticClient([]);
        $client->put(1234, [
            'data' => 'yes please'
        ]);
    }
}
