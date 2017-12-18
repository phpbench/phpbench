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
     * @var string
     */
    private $apiKey;

    /**
     * @var array
     */
    private $url;

    public function __construct(string $url, string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->url = $url;
    }

    public function post(string $url, string $data): array
    {
        $url = $this->url . $url;
        $curl = \curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $this->apiKey,
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
        ];
        $options[CURLOPT_POSTFIELDS] = $data;
        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        if ($error = curl_error($curl)) {
            throw new RuntimeException(sprintf(
                'Could not talk to server: "%s"',
                $error
            ));
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $decoded = json_decode($response, true);

        if ($status !== 200) {
            throw new RuntimeException(sprintf(
                'Endpoint returned non-200 status: %s for %s',
                $status, $url
            ));

        }


        if (null === $decoded) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON: %s',
                json_last_error_msg()
            ), null, new RuntimeException($response));
        }

        return $decoded;
    }
}
