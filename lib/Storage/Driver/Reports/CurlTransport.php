<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Storage\Driver\Reports;

use RuntimeException;

class CurlTransport implements TransportInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $apiKey;

    public function __construct(array $config, string $apiKey)
    {
        $this->config = $config;
        $this->apiKey = $apiKey;
    }

    public function post(string $url, array $data): array
    {
        $config = $this->resolveConfig($this->config);
        $curl = \curl_init();
        $options = [
            CURLOPT_URL => $config['base_url'] . '/api/v1' . $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->apiKey,
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

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($status !== 200) {
            throw new RuntimeException(sprintf(
                'Endpoint returned non-200 status: %s with content "%s"',
                $status, $response
            ));
        }

        $decoded = json_decode($response, true);

        if (null === $decoded) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON: %s',
                json_last_error_msg()
            ), null, new RuntimeException($response));
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
