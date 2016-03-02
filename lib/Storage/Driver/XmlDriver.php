<?php

namespace PhpBench\Storage\Driver;

use PhpBench\Storage\DriverInterface;
use PhpBench\Serializer\XmlEncoder;
use PhpBench\Model\SuiteCollection;
use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Storage\Driver\Xml\Persister;
use PhpBench\Storage\Driver\Xml\HistoryIterator;

class XmlDriver implements DriverInterface
{
    private $persister;
    private $path;

    public function __construct(Persister $persister, $path)
    {
        $this->persister = $persister;
        $this->path = $path;
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
    public function query(Constraint $constraint)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function history()
    {
        return new HistoryIterator($this->path);
    }
}
