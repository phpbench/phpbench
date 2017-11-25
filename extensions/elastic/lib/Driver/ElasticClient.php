<?php

namespace PhpBench\Extensions\Elastic\Driver;

use PhpBench\Model\SuiteCollection;
use RuntimeException;
use InvalidArgumentException;

class ElasticClient
{
    const TYPE_VARIANT = 'variant';
    const TYPE_ITERATION = 'iteration';

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
            'index' => 'phpbench'
        ];

        if ($diff = array_diff(array_keys($options), array_keys($defaults))) {
            throw new InvalidArgumentException(sprintf(
                'Unknown configuration options "%s", known parameters: "%s"',
                $diff, array_keys($defaults)
            ));
        }

        $this->options = array_merge($defaults, $options);

    }

    public function put(string $type, string $id, array $data)
    {
        $this->request('PUT', $type . '/' . $id, $data);
    }

    public function get(string $type, string $id, array $data = [])
    {
        return $this->request('GET', $type . '/' . $id, $data);
    }

    public function install(array $config)
    {
        $this->request('PUT', null, $config);
    }

    public function purge()
    {
        $this->request('DELETE');
    }

    private function request(string $method, string $suffix = null, array $data = [])
    {
        $curl = \curl_init();
        $options = [
            CURLOPT_URL => $this->indexUrl($suffix),
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

        if (isset($response['error'])) {
            throw new RuntimeException(sprintf(
                'Elastic returned an error: "%s"',
                json_encode($response['error'])
            ));
        }

        return $response;
    }

    private function indexUrl($suffix = null): string
    {
        $url = sprintf(
            '%s://%s:%s/%s/%s',
            $this->options['scheme'],
            $this->options['host'],
            $this->options['port'],
            $this->options['index'],
            $suffix
        );

        return $url;
    }
}
