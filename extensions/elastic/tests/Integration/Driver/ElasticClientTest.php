<?php

namespace PhpBench\Extensions\Elastic\Tests\Integration\Driver;

use PHPUnit\Framework\TestCase;
use PhpBench\Extensions\Elastic\Tests\Integration\ElasticTestCase;

class Driver extends ElasticTestCase
{
    public function testPutGet()
    {
        $document = [
            'data' => 'yes please'
        ];
        $client = $this->createClient();
        $client->put(1234, $document);
        $this->assertEquals($document, $client->get(1234));
    }

    public function testSuites()
    {
        $client->put(1, [ 'suite' => 10 ]);
        $client->put(2, [ 'suite' => 20 ]);

        $client = $this->createClient();
        $suites = $this->client->suites();
    }
}
