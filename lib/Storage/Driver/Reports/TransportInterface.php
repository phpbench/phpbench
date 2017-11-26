<?php

namespace PhpBench\Storage\Driver\Reports;

interface TransportInterface
{
    public function post(string $url, array $data): array;
}
