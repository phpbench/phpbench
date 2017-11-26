<?php

namespace PhpBench\Storage\Driver\Reports;

use PhpBench\Model\Suite;
use PhpBench\Extensions\Elastic\Encoder\DocumentEncoder;
use PhpBench\Storage\Driver\Reports\TransportInterface;

class ReportClient
{
    /**
     * @var bool
     */
    private $storeIterations;

    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var DocumentEncoder
     */
    private $encoder;

    public function __construct(TransportInterface $transport, DocumentEncoder $encoder, bool $storeIterations)
    {
        $this->storeIterations = $storeIterations;
        $this->transport = $transport;
        $this->encoder = $encoder;
    }

    public function post(Suite $suite)
    {
        $suiteArray = $this->encoder->aggregationsFromSuite($suite);
        $this->transport->post('/suite', $suiteArray);

        if (false === $this->storeIterations) {
            return;
        }

        $iterationsArray = $this->encoder->iterationsFromSuite($suite);
        $this->transport->post('/iterations', $iterationsArray);
    }
}
