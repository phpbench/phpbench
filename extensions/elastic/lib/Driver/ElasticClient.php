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
            'index_prefix' => 'phpbench_'
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
        $this->request('PUT', $type, 'doc/' . $id, $data);
    }

    public function get(string $type, string $id, array $data = [])
    {
        return $this->request('GET', $type, 'doc/' . $id, $data);
    }

    public function install(array $config)
    {
        $this->request('PUT', self::TYPE_VARIANT, '', $config);
    }

    public function purge()
    {
        $this->request('DELETE', self::TYPE_VARIANT);
        $this->request('DELETE', self::TYPE_ITERATION);
    }

    private function request(string $method, string $type, string $suffix = null, array $data = [])
    {
        $curl = \curl_init();
        $url = $this->indexUrl($type, $suffix);
        $options = [
            CURLOPT_URL => $url,
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

    private function indexUrl(string $type, string $suffix = null): string
    {
        $url = sprintf(
            '%s://%s:%s/%s/%s',
            $this->options['scheme'],
            $this->options['host'],
            $this->options['port'],
            $this->options['index_prefix'] . $type,
            $suffix
        );

        return $url;
    }
}
