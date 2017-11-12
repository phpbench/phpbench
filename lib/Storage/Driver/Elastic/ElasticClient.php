<?php

namespace PhpBench\Storage\Driver\Elastic;

use PhpBench\Model\SuiteCollection;

class ElasticClient
{
    /**
     * @var array
     */
    private $options;

    public function __construct($options = [])
    {
        $defaults = [
            'scheme' => 'http',
            'host' => 'localhost',
            'port' => 9200,
            'index' => 'phpbench',
            'type' => 'suite_collection',
        ];
        $this->options = array_merge($defaults, $options);

    }

    public function put(string $id, array $data)
    {
        $curl = \curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->elasticUrl($id),
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
        ]);

        $response = curl_exec($curl);
    }

    private function elasticUrl($id): string
    {
        return sprintf(
            '%s://%s:%s/%s/%s/%s',
            $this->options['scheme'],
            $this->options['host'],
            $this->options['port'],
            $this->options['index'],
            $this->options['type'],
            $id
        );
    }

}
