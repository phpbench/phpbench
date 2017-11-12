<?php

namespace PhpBench\Extensions\Elastic\Tests\Integration;

use PHPUnit\Framework\TestCase;
use PhpBench\Extensions\Elastic\Driver\ElasticClient;

class Driver extends TestCase
{
    public function testElasticClient()
    {
        $client = new ElasticClient([]);
        $client->put(1234, [
            'data' => 'yes please'
        ]);
    }
}
