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

namespace PhpBench\Extensions\Reports\Driver;

use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Registry;
use PhpBench\Serializer\XmlEncoder;
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

    /**
     * @var XmlEncoder
     */
    private $xmlEncoder;

    public function __construct(
        ReportsClient $client,
        Registry $storageRegistry,
        XmlEncoder $xmlEncoder,
        string $innerStorageName
    ) {
        $this->client = $client;
        $this->storageRegistry = $storageRegistry;
        $this->innerStorageName = $innerStorageName;
        $this->xmlEncoder = $xmlEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function store(SuiteCollection $collection)
    {
        $suiteDocument = $this->xmlEncoder->encode($collection);
        $response = $this->client->post('/import', $suiteDocument->dump());

        $this->innerDriver()->store($collection);

        return 'Report: ' . $response['suite_url'];
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
