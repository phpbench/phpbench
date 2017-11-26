<?php

namespace PhpBench\Storage\Driver\Reports;

use PhpBench\Storage\DriverInterface;
use PhpBench\Model\SuiteCollection;
use PhpBench\Expression\Constraint\Constraint;
use BadMethodCallException;
use PhpBench\Extensions\Elastic\Driver\ElasticClient;
use PhpBench\Model\Suite;
use PhpBench\Serializer\ArrayEncoder;
use PhpBench\Extensions\Elastic\Encoder\DocumentEncoder;
use PhpBench\Extensions\Elastic\Encoder\DocumentDecoder;

class ReportsDriver implements DriverInterface
{
    /**
     * @var DocumentEncoder
     */
    private $documentEncoder;

    /**
     * @var DriverInterface
     */
    private $innerDriver;

    /**
     * @var bool
     */
    private $storeIterations;

    /**
     * @var ReportClientInterface
     */
    private $client;

    public function __construct(
        ReportClientInterface $client,
        DriverInterface $innerDriver,
        DocumentEncoder $documentEncoder = null,
        bool $storeIterations = false
    )
    {
        $this->innerDriver = $innerDriver;
        $this->documentEncoder = $documentEncoder ?: new DocumentEncoder();
        $this->storeIterations = $storeIterations;
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function store(SuiteCollection $collection)
    {
        /** @var Suite $suite */
        foreach ($collection as $suite) {
            $this->client->post($suite);
        }

        $this->innerDriver->store($collection);
    }

    /**
     * {@inheritDoc}
     */
    public function query(Constraint $constraint)
    {
        return $this->innerDriver->query($constraint);
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($suiteId)
    {
        return $this->innerDriver->fetch($suiteId);
    }

    /**
     * {@inheritDoc}
     */
    public function has($suiteId)
    {
        return $this->innerDriver->has($suiteId);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($suiteId)
    {
        $this->innerDriver->delete($suiteId);
    }

    /**
     * {@inheritDoc}
     */
    public function history()
    {
        return $this->innerDriver->history();
    }
}
