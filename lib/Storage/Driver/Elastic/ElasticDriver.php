<?php

namespace PhpBench\Storage\Driver\Elastic;

use PhpBench\Storage\DriverInterface;
use PhpBench\Model\SuiteCollection;
use PhpBench\Expression\Constraint\Constraint;
use BadMethodCallException;
use PhpBench\Storage\Driver\Elastic\ElasticClient;
use PhpBench\Model\Suite;


class ElasticDriver implements DriverInterface
{
    /**
     * @var ElasticClient
     */
    private $elasticClient;

    public function __construct(ElasticClient $elasticClient, ArrayEncoder $arrayEncoder)
    {
        $this->elasticClient = $elasticClient;
        $this->arrayEncoder = $arrayEncoder;
    }

    /**
     * {@inheritDoc}
     */
    public function store(SuiteCollection $collection)
    {
        /** @var Suite $suite */
        foreach ($collection as $suite) {
            $this->elasticClient->put($suite->getUuid(), $suite->toArray());
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
