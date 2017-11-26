<?php

namespace PhpBench\Storage\Driver\Reports;

use BadMethodCallException;
use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Extensions\Elastic\Driver\ElasticClient;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Storage\DriverInterface;
use PhpBench\Registry\Registry;

class ReportsDriver implements DriverInterface
{
    /**
     * @var DriverInterface
     */
    private $innerDriver;

    /**
     * @var ReportClientInterface
     */
    private $client;

    /**
     * @var Registry
     */
    private $storageRegistry;

    /**
     * @var string
     */
    private $innerStorageName;

    public function __construct(
        ReportClient $client,
        Registry $storageRegistry,
        string $innerStorageName
    )
    {
        $this->client = $client;
        $this->storageRegistry = $storageRegistry;
        $this->innerStorageName = $innerStorageName;
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

        $this->innerDriver()->store($collection);
    }

    /**
     * {@inheritDoc}
     */
    public function query(Constraint $constraint)
    {
        return $this->innerDriver()->query($constraint);
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($suiteId)
    {
        return $this->innerDriver()->fetch($suiteId);
    }

    /**
     * {@inheritDoc}
     */
    public function has($suiteId)
    {
        return $this->innerDriver()->has($suiteId);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($suiteId)
    {
        $this->innerDriver()->delete($suiteId);
    }

    /**
     * {@inheritDoc}
     */
    public function history()
    {
        return $this->innerDriver()->history();
    }

    private function innerDriver()
    {
        if ($this->innerDriver) {
            return $this->innerDriver;
        }

        return $this->storageRegistry->getService($this->innerStorageName);
    }
}
