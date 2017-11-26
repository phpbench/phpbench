<?php

namespace PhpBench\Storage\Driver\Reports;

use RuntimeException;

class CurlTransport implements TransportInterface
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function post(string $url, array $data): array
    {
        $config = $this->resolveConfig($this->config);
        $curl = \curl_init();
        $options = [
            CURLOPT_URL => $config['base_url'] . $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true,
        ];
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        if ($error = curl_error($curl)) {
            throw new RuntimeException(sprintf(
                'Could not talk to elastic search: "%s"',
                $error
            ));
        }

        $decoded = json_decode($response, true);

        if (null === $decoded) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON: %s',
                json_last_error_msg()
            ));
        }

        return $decoded;
    }

    private function resolveConfig(array $config)
    {
        $defaults = [
            'base_url' => null,
        ];

        if ($diff = array_diff(array_keys($config), array_keys($defaults))) {
            throw new RuntimeException(sprintf(
                'Unknown connection config keys "%s", known keys: "%s"',
                implode('", "', $diff), implode('", "', array_keys($defaults))
            ));
        }

        $config = array_merge($defaults, $config);

        if (null === $config['base_url']) {
            throw new RuntimeException(sprintf(
                'base_url must be configured in order to use the "reports" storage'
            ));
        }

        return $config;
    }
}
