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
            'type' => 'variant',
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
        $this->request('PUT', $id, $data);
    }

    public function search(array $query)
    {
        $response = $this->request('GET','_search', $query);

        if (isset($response['error'])) {
            throw new RuntimeException(sprintf(
                'Elastic returned an error: "%s"',
                json_encode($response['error'])
            ));
        }

        $hits = $response['hits'];

        return array_map(function (array $response) {
            return $response['_source'];
        }, $hits['hits']);
    }

    private function request(string $method, string $suffix = null, array $data = [])
    {
        $curl = \curl_init();
        $options = [
            CURLOPT_URL => $this->elasticUrl($suffix),
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
        ];

        if ($data) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

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

        return $response;
    }

    private function elasticUrl(string $suffix): string
    {
        $url = sprintf(
            '%s://%s:%s/%s/%s/%s',
            $this->options['scheme'],
            $this->options['host'],
            $this->options['port'],
            $this->options['index'],
            $this->options['type'],
            $suffix
        );

        return $url;
    }
}
