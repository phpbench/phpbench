<?php

namespace PhpBench\Storage\Driver\Elastic;

use PhpBench\Storage\DriverInterface;
use PhpBench\Model\SuiteCollection;
use PhpBench\Expression\Constraint\Constraint;
use BadMethodCallException;
use PhpBench\Storage\Driver\Elastic\ElasticClient;
use PhpBench\Model\Suite;
use PhpBench\Serializer\ArrayEncoder;
use PhpBench\Serializer\DocumentEncoder;

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


    public function __construct(ElasticClient $elasticClient, DocumentEncoder $documentEncoder)
    {
        $this->elasticClient = $elasticClient;
        $this->documentEncoder = $documentEncoder;
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
    }

    /**
     * {@inheritDoc}
     */
    public function has($runId)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function delete($runId)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function history()
    {
    }
}
