<?php

namespace PhpBench\Extensions\Elastic\Tests\Integration;

use PhpBench\Extensions\Elastic\Driver\ElasticClient;
use PHPUnit\Framework\TestCase;

class ElasticTestCase extends TestCase
{
    protected function createClient(): ElasticClient
    {
        return new ElasticClient([
            'port' => getenv('PHPBENCH_ELASTIC_PORT') ?: 9200
        ]);
    }
}
