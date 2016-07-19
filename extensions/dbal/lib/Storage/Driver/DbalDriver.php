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

namespace PhpBench\Extensions\Dbal\Storage\Driver;

use PhpBench\Expression\Constraint\Comparison;
use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\HistoryIterator;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Loader;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Persister;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Repository;
use PhpBench\Model\SuiteCollection;
use PhpBench\Storage\DriverInterface;

/**
 * Dbal Driver.
 */
class DbalDriver implements DriverInterface
{
    private $loader;
    private $persister;
    private $repository;

    public function __construct(
        Loader $loader,
        Persister $persister,
        Repository $repository
    ) {
        $this->loader = $loader;
        $this->persister = $persister;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function query(Constraint $constraint)
    {
        return $this->loader->load($constraint);
    }

    /**
     * {@inheritdoc}
     */
    public function store(SuiteCollection $collection)
    {
        $this->persister->persist($collection);
    }

    /**
     * {@inheritdoc}
     */
    public function has($runId)
    {
        return $this->repository->hasRun($runId);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($runId)
    {
        $this->repository->deleteRun($runId);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($runId)
    {
        if (!$this->repository->hasRun($runId)) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find suite with run ID "%s"', $runId
            ));
        }

        $comparison = new Comparison('$eq', 'run', $runId);
        $collection = $this->query($comparison);

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function history()
    {
        return new HistoryIterator($this->repository);
    }
}
