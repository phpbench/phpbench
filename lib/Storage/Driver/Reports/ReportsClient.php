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

use PhpBench\Model\Suite;
use PhpBench\Serializer\ElasticEncoder;

class ReportsClient
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
     * @var ElasticEncoder
     */
    private $encoder;

    public function __construct(TransportInterface $transport, ElasticEncoder $encoder, bool $storeIterations)
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
