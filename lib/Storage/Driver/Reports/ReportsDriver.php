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

use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Registry;
use PhpBench\Storage\DriverInterface;

class ReportsDriver implements DriverInterface
{
    /**
     * @var DriverInterface
     */
    private $innerDriver;

    /**
     * @var ReportsClient
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
        ReportsClient $client,
        Registry $storageRegistry,
        string $innerStorageName
    ) {
        $this->client = $client;
        $this->storageRegistry = $storageRegistry;
        $this->innerStorageName = $innerStorageName;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function query(Constraint $constraint)
    {
        return $this->innerDriver()->query($constraint);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($suiteId)
    {
        return $this->innerDriver()->fetch($suiteId);
    }

    /**
     * {@inheritdoc}
     */
    public function has($suiteId)
    {
        return $this->innerDriver()->has($suiteId);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($suiteId)
    {
        $this->innerDriver()->delete($suiteId);
    }

    /**
     * {@inheritdoc}
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
