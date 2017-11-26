<?php

namespace PhpBench\Storage\Driver\Reports;

class CurlTransport implements TransportInterface
{
    public function post(string $url, array $data): array
    {
        $curl = \curl_init();
        $url = $this->indexUrl($type, $suffix);
        $options = [
            CURLOPT_URL => $url,
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

        return $response;
    }
}
