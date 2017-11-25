<?php

namespace PhpBench\Extensions\Elastic\Driver;

use PhpBench\Storage\DriverInterface;
use PhpBench\Model\SuiteCollection;
use PhpBench\Expression\Constraint\Constraint;
use BadMethodCallException;
use PhpBench\Extensions\Elastic\Driver\ElasticClient;
use PhpBench\Model\Suite;
use PhpBench\Serializer\ArrayEncoder;
use PhpBench\Extensions\Elastic\Encoder\DocumentEncoder;
use PhpBench\Extensions\Elastic\Encoder\DocumentDecoder;

class ElasticDriver implements DriverInterface
{
    /**
     * @var ElasticClient
     */
    private $elasticClient;

    /**
     * @var ArrayEncoder
     */
    private $documentEncoder;

    /**
     * @var DriverInterface
     */
    private $innerDriver;

    public function __construct(
        ElasticClient $elasticClient,
        DriverInterface $innerDriver,
        DocumentEncoder $documentEncoder = null
    )
    {
        $this->elasticClient = $elasticClient;
        $this->innerDriver = $innerDriver;
        $this->documentEncoder = $documentEncoder ?: new DocumentEncoder();
    }

    /**
     * {@inheritDoc}
     */
    public function store(SuiteCollection $collection)
    {
        /** @var Suite $suite */
        foreach ($collection as $suite) {
            foreach ($this->documentEncoder->documentsFromSuite($suite) as $id => $document) {
                $this->elasticClient->put(
                    $id,
                    $document
                );
            }
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
