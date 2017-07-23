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

namespace PhpBench\Tests\Unit\Storage\Driver;

use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Loader;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Persister;
use PhpBench\Extensions\Dbal\Storage\Driver\Dbal\Repository;
use PhpBench\Extensions\Dbal\Storage\Driver\DbalDriver;
use PhpBench\Model\SuiteCollection;
use PHPUnit\Framework\TestCase;

class DbalDriverTest extends TestCase
{
    private $loader;
    private $persister;
    private $repository;
    private $constraint;
    private $driver;

    public function setUp()
    {
        $this->loader = $this->prophesize(Loader::class);
        $this->persister = $this->prophesize(Persister::class);
        $this->repository = $this->prophesize(Repository::class);

        $this->constraint = $this->prophesize(Constraint::class);
        $this->suiteCollection = $this->prophesize(SuiteCollection::class);

        $this->driver = new DbalDriver(
            $this->loader->reveal(),
            $this->persister->reveal(),
            $this->repository->reveal()
        );
    }

    /**
     * It should return a SuiteCollection for a given query.
     */
    public function testQuery()
    {
        $this->loader->load(
            $this->constraint->reveal()
        )->willReturn($this->suiteCollection->reveal());

        $collection = $this->driver->query($this->constraint->reveal());
        $this->assertSame($this->suiteCollection->reveal(), $collection);
    }

    /**
     * It should store a suite collection.
     */
    public function testStore()
    {
        $this->persister->persist($this->suiteCollection->reveal())->shouldBeCalled();
        $this->driver->store($this->suiteCollection->reveal());
    }

    /**
     * It should throw an exception if getting a non-existant run.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not find suite with run
     */
    public function testFetchNonExisting()
    {
        $this->repository->hasRun('1234')->willReturn(false);
        $this->driver->fetch('1234');
    }

    /**
     * It should return a history iterator.
     */
    public function testHistory()
    {
        $iterator = $this->driver->history();
        $this->assertInstanceOf('PhpBench\Extensions\Dbal\Storage\Driver\Dbal\HistoryIterator', $iterator);
    }
}
