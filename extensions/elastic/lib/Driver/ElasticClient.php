<?php

namespace PhpBench\Extensions\Elastic\Driver;

use PhpBench\Model\SuiteCollection;
use RuntimeException;
use InvalidArgumentException;

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

        if ($diff = array_diff(array_keys($options), array_keys($defaults))) {
            throw new InvalidArgumentException(sprintf(
                'Unknown configuration options "%s", known parameters: "%s"',
                $diff, array_keys($defaults)
            ));
        }

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
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($curl);

        if ($error = curl_error($curl)) {
            throw new RuntimeException(sprintf(
                'Could not talk to elastic search: "%s"',
                $error
            ));
        }

        $response = json_decode($response, true);

        if (false === $response) {
            throw new RuntimeException(sprintf(
                'Could not decode elastic response: "%s"',
                json_last_error_msg()
            ));
        }
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
