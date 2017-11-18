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

    public function __construct(ElasticClient $elasticClient, DocumentEncoder $documentEncoder = null)
    {
        $this->elasticClient = $elasticClient;
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
    }

    /**
     * {@inheritDoc}
     */
    public function query(Constraint $constraint)
    {
        throw new BadMethodCallException(sprintf(
            'Querying not supported'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function fetch($runId)
    {
        throw new BadMethodCallException(sprintf(
            'Fetch not supported'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function has($runId)
    {
        throw new BadMethodCallException(sprintf(
            'Has not supported'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function delete($runId)
    {
        throw new BadMethodCallException(sprintf(
            'Delete not supported'
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function history()
    {
        throw new BadMethodCallException(sprintf(
            'History not supported'
        ));
    }
}
