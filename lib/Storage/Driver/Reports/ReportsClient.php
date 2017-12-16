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
use PhpBench\Serializer\XmlEncoder;
use PhpBench\Model\SuiteCollection;

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
     * @var XmlEncoder
     */
    private $encoder;

    public function __construct(TransportInterface $transport, XmlEncoder $encoder, bool $storeIterations)
    {
        $this->storeIterations = $storeIterations;
        $this->transport = $transport;
        $this->encoder = $encoder;
    }

    public function post(SuiteCollection $suite)
    {
        $suiteDocument = $this->encoder->encode($suite);
        $this->transport->post('/import', $suiteDocument->dump());
    }
}
